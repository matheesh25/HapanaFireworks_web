<?php
session_start();
include("config.php");
if(!isset($_GET['id'])){
    die("Order not found");
}
$id  = intval($_GET['id']);
$res = mysqli_query($conn,"SELECT * FROM orders WHERE id='$id'");
$row = mysqli_fetch_assoc($res);
if(!$row){
    die("Order not found");
}

// CALCULATIONS
// $row['total'] is already the grand total (subtotal + delivery) saved at checkout
$grand_total = floatval($row['total']);
$shipping    = 300;
$discount    = 0;
$subtotal    = $grand_total - $shipping + $discount;

// Status config
$statusMap = [
  'pending'   => ['class'=>'status-pending',   'icon'=>'hourglass-split',    'label'=>'Pending'],
  'approved'  => ['class'=>'status-approved',  'icon'=>'check-circle-fill',  'label'=>'Approved'],
  'completed' => ['class'=>'status-completed', 'icon'=>'bag-check-fill',     'label'=>'Completed'],
  'cancelled' => ['class'=>'status-cancelled', 'icon'=>'x-circle-fill',      'label'=>'Cancelled'],
];
$s = strtolower($row['status']);
$statusCfg = $statusMap[$s] ?? $statusMap['cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hapana Fireworks – Order #ORD<?= $row['id'] ?></title>
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
   PAGE
=========================== */
.detail-page {
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

/* Back button */
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
   ORDER HERO ROW
=========================== */
.order-hero {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  flex-wrap: wrap;
}
.order-id-block {}
.order-id-label {
  font-size: 0.72rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 600;
  margin-bottom: 4px;
}
.order-id-val {
  font-size: 1.3rem;
  font-weight: 800;
  color: var(--gold);
  letter-spacing: 0.5px;
}
.order-date {
  font-size: 0.82rem;
  color: var(--text-muted);
  margin-top: 4px;
}

/* Status badge */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 8px 18px;
  border-radius: var(--radius-pill);
  letter-spacing: 0.5px;
  text-transform: uppercase;
}
.status-pending   { background: rgba(255,193,7,0.15);  color: var(--gold);    border: 1px solid rgba(255,193,7,0.3); }
.status-approved  { background: rgba(34,197,94,0.12);  color: #4ade80;        border: 1px solid rgba(34,197,94,0.3); }
.status-completed { background: rgba(59,130,246,0.12); color: #60a5fa;        border: 1px solid rgba(59,130,246,0.3); }
.status-cancelled { background: rgba(239,68,68,0.12);  color: #f87171;        border: 1px solid rgba(239,68,68,0.3); }

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
   PRODUCT ROW
=========================== */
.product-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px;
  padding: 18px 20px;
  transition: all var(--transition);
}
.product-row:hover {
  border-color: rgba(255,193,7,0.25);
  background: rgba(255,193,7,0.04);
}
.product-icon {
  width: 46px; height: 46px;
  background: var(--gold-dim);
  border: 1px solid var(--gold-border);
  border-radius: 13px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.product-icon i { color: var(--gold); font-size: 1.2rem; }
.product-info { flex: 1; min-width: 0; }
.product-name {
  font-size: 0.95rem;
  font-weight: 600;
  color: #f0f0f0;
  margin-bottom: 3px;
}
.product-qty {
  font-size: 0.78rem;
  color: var(--text-muted);
}
.product-price {
  font-size: 1rem;
  font-weight: 700;
  color: var(--gold);
  white-space: nowrap;
}

/* ===========================
   PRICE SUMMARY
=========================== */
.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.price-row:last-of-type { border-bottom: none; }
.price-lbl { font-size: 0.87rem; color: var(--text-muted); }
.price-val { font-size: 0.87rem; color: #e0e0e0; font-weight: 500; }
.price-val.discount { color: #4ade80; }

.price-total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0 0;
  margin-top: 8px;
  border-top: 1px solid var(--gold-border);
}
.price-total-lbl { font-size: 1rem; font-weight: 700; color: #fff; }
.price-total-val { font-size: 1.25rem; font-weight: 800; color: var(--gold); }

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
   TIMELINE / STATUS TRACKER
=========================== */
.timeline {
  display: flex;
  align-items: flex-start;
  gap: 0;
  padding: 8px 0;
  overflow-x: auto;
}
.tl-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  min-width: 70px;
  position: relative;
}
.tl-step::before {
  content: "";
  position: absolute;
  top: 16px;
  left: 50%;
  width: 100%;
  height: 2px;
  background: rgba(255,255,255,0.08);
  z-index: 0;
}
.tl-step:last-child::before { display: none; }
.tl-dot {
  width: 34px; height: 34px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.85rem;
  border: 2px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.04);
  color: #555;
  position: relative;
  z-index: 1;
  transition: all var(--transition);
  flex-shrink: 0;
}
.tl-dot.done {
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  border-color: var(--gold);
  color: #000;
  box-shadow: 0 0 12px rgba(255,193,7,0.4);
}
.tl-step.active .tl-dot {
  border-color: var(--gold);
  color: var(--gold);
  background: var(--gold-dim);
  box-shadow: 0 0 12px rgba(255,193,7,0.25);
}
.tl-label {
  font-size: 0.68rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  color: #555;
  margin-top: 8px;
  text-align: center;
}
.tl-step.done .tl-label,
.tl-step.active .tl-label { color: var(--gold); }

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

@keyframes fadeIn {
  from { opacity:0; transform:translateY(18px); }
  to   { opacity:1; transform:translateY(0); }
}

/* ===========================
   MOBILE
=========================== */
@media (max-width:767.98px) {
  .detail-page { padding: 100px 0 60px; }
  .glass-card  { padding: 22px 16px; }
  .order-hero  { flex-direction: column; gap: 12px; }
  .info-grid   { grid-template-columns: 1fr 1fr; }
  .action-row  { flex-direction: column; align-items: stretch; }
  .btn-primary-gold, .btn-outline-gold { justify-content: center; }
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

<!-- DETAIL PAGE -->
<main class="detail-page">
  <div class="container" style="max-width:760px;">

    <!-- Page Header -->
    <div class="page-header fade-in">
      <p class="page-label">Order Receipt</p>
      <h1>Order Details</h1>
    </div>

    <!-- Back -->
    <div class="fade-in">
      <a href="myOrders.php" class="btn-back">
        <i class="bi bi-arrow-left"></i> Back to Orders
      </a>
    </div>

    <!-- ORDER SUMMARY CARD -->
    <div class="glass-card fade-in-d1">

      <!-- Order ID + Status -->
      <div class="order-hero">
        <div class="order-id-block">
          <p class="order-id-label">Order ID</p>
          <p class="order-id-val">#ORD<?= $row['id'] ?></p>
          <p class="order-date">
            <i class="bi bi-calendar3" style="color:var(--gold);margin-right:5px;"></i>
            <?= date("d M Y, g:i A", strtotime($row['created_at'])) ?>
          </p>
        </div>
        <span class="status-badge <?= $statusCfg['class'] ?>">
          <i class="bi bi-<?= $statusCfg['icon'] ?>"></i>
          <?= $statusCfg['label'] ?>
        </span>
      </div>

      <hr class="gold-divider">

      <!-- Order Info Grid -->
      <div class="section-title"><i class="bi bi-person-lines-fill"></i> Customer Info</div>
      <div class="info-grid" style="margin-bottom:0;">
        <div class="info-item">
          <p class="info-lbl">Full Name</p>
          <p class="info-val"><?= htmlspecialchars($row['name']) ?></p>
        </div>
        <div class="info-item">
          <p class="info-lbl">Phone</p>
          <p class="info-val"><?= htmlspecialchars($row['phone']) ?></p>
        </div>
        <div class="info-item" style="grid-column: 1 / -1;">
          <p class="info-lbl">Delivery Address</p>
          <p class="info-val"><?= htmlspecialchars($row['address']) ?></p>
        </div>
        <div class="info-item">
          <p class="info-lbl">Payment Method</p>
          <p class="info-val"><?= $row['payment'] === 'paid' ? '💳 Card' : '💵 Cash on Delivery' ?></p>
        </div>
      </div>

    </div>

    <!-- ORDER TRACKER -->
    <div class="glass-card fade-in-d2">
      <div class="section-title"><i class="bi bi-signpost-2"></i> Order Progress</div>
      <?php
        $steps = [
          ['key'=>'placed',    'icon'=>'receipt',           'label'=>'Placed'],
          ['key'=>'approved',  'icon'=>'check-circle',      'label'=>'Approved'],
          ['key'=>'completed', 'icon'=>'bag-check',         'label'=>'Completed'],
        ];
        $order = ['placed','approved','completed'];
        $currentIdx = array_search($s, $order);
        if($currentIdx === false) $currentIdx = -1; // cancelled
        // 'pending' maps to 'placed'
        if($s === 'pending') $currentIdx = 0;
      ?>
      <div class="timeline">
        <?php foreach($steps as $i => $step):
          $done   = ($i < $currentIdx);
          $active = ($i === $currentIdx);
          $cls    = $done ? 'done' : ($active ? 'active' : '');
        ?>
          <div class="tl-step <?= $done ? 'done' : ($active ? 'active' : '') ?>">
            <div class="tl-dot <?= $done ? 'done' : '' ?>">
              <i class="bi bi-<?= $done ? 'check-lg' : $step['icon'] ?>"></i>
            </div>
            <span class="tl-label"><?= $step['label'] ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- PRODUCT + PRICING CARD -->
    <div class="glass-card fade-in-d3">

      <!-- Product -->
      <div class="section-title"><i class="bi bi-box-seam"></i> Item Ordered</div>
      <div class="product-row">
        <div class="product-icon"><i class="bi bi-stars"></i></div>
        <div class="product-info">
          <p class="product-name"><?= htmlspecialchars($row['product']) ?></p>
          <p class="product-qty">Quantity: <?= intval($row['quantity']) ?></p>
        </div>
        <span class="product-price">Rs. <?= number_format($subtotal) ?></span>
      </div>

      <hr class="gold-divider">

      <!-- Price Breakdown -->
      <div class="section-title"><i class="bi bi-receipt-cutoff"></i> Price Breakdown</div>
      <div class="price-row">
        <span class="price-lbl">Subtotal</span>
        <span class="price-val">Rs. <?= number_format($subtotal) ?></span>
      </div>
      <?php if($discount > 0): ?>
      <div class="price-row">
        <span class="price-lbl">Discount</span>
        <span class="price-val discount">− Rs. <?= number_format($discount) ?></span>
      </div>
      <?php endif; ?>
      <div class="price-row">
        <span class="price-lbl">Delivery</span>
        <span class="price-val">Rs. <?= number_format($shipping) ?></span>
      </div>
      <div class="price-total-row">
        <span class="price-total-lbl">Grand Total</span>
        <span class="price-total-val">Rs. <?= number_format($grand_total) ?></span>
      </div>

    </div>

    <!-- ACTION BUTTONS -->
    <div class="action-row fade-in-d3">
      <a href="track.php?id=<?= $row['id'] ?>" class="btn-primary-gold">
        <i class="bi bi-truck"></i> Track Order
      </a>
      <a href="myOrders.php" class="btn-outline-gold">
        <i class="bi bi-arrow-left"></i> Back to Orders
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
  constructor(){ this.x=Math.random()*canvas.width; this.y=canvas.height; this.targetY=Math.random()*canvas.height*0.5; this.color=`hsl(${Math.random()*360},100%,60%)`; this.speed=5; this.exploded=false; }
  update(){ if(!this.exploded){ this.y-=this.speed; if(this.y<=this.targetY){ this.exploded=true; for(let i=0;i<50;i++) particles.push(new Particle(this.x,this.y,this.color)); } } }
  draw(){ if(!this.exploded){ ctx.fillStyle=this.color; ctx.fillRect(this.x,this.y,3,8); } }
}
class Particle {
  constructor(x,y,color){ this.x=x; this.y=y; this.color=color; this.radius=2; this.speedX=(Math.random()-0.5)*6; this.speedY=(Math.random()-0.5)*6; this.gravity=0.05; this.alpha=1; }
  update(){ this.x+=this.speedX; this.y+=this.speedY; this.speedY+=this.gravity; this.alpha-=0.01; }
  draw(){ ctx.globalAlpha=this.alpha; ctx.fillStyle=this.color; ctx.beginPath(); ctx.arc(this.x,this.y,this.radius,0,Math.PI*2); ctx.fill(); ctx.globalAlpha=1; }
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