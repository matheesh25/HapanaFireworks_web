<?php
session_start();
include("config.php");
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
$id  = $_SESSION['user_id'];
$res = mysqli_query($conn,"SELECT * FROM orders WHERE customer_id='$id' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hapana Fireworks – My Orders</title>
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

/* ===========================
   RESET & BASE
=========================== */
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
.navbar .nav-link.active {
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000 !important;
  font-weight: 600;
  box-shadow: 0 0 14px rgba(255,193,7,0.3);
}
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
.orders-page {
  min-height: 100vh;
  padding: 110px 0 70px;
}

/* Page header */
.page-header {
  text-align: center;
  margin-bottom: 44px;
}
.header-icon-wrap {
  width: 76px; height: 76px;
  background: var(--gold-dim);
  border: 1px solid var(--gold-border);
  border-radius: 22px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 18px;
  box-shadow: 0 0 24px rgba(255,193,7,0.15);
}
.header-icon-wrap i { font-size: 2rem; color: var(--gold); }
.page-label {
  color: #ffd86b;
  font-size: 0.8rem;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  font-weight: 600;
  margin-bottom: 8px;
}
.page-header h1 {
  font-size: clamp(1.7rem, 5vw, 2.4rem);
  font-weight: 800;
  color: #fff;
  text-shadow: 0 0 18px rgba(255,193,7,0.2);
  margin-bottom: 8px;
}
.page-header p {
  color: var(--text-muted);
  font-size: 0.9rem;
}

/* Stats bar */
.stats-bar {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  justify-content: center;
  margin-bottom: 36px;
}
.stat-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-card);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-pill);
  padding: 8px 18px;
  font-size: 0.82rem;
  color: var(--text-muted);
  font-weight: 500;
  backdrop-filter: blur(8px);
}
.stat-pill strong { color: var(--gold); font-size: 1rem; }

/* ===========================
   GLASS CARD WRAPPER
=========================== */
.glass-wrap {
  background: var(--bg-card);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-card);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.35);
  padding: 32px 28px;
}
.wrap-title {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--gold);
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.wrap-divider {
  border: none;
  border-top: 1px solid var(--gold-border);
  margin: 0 0 24px;
}

/* ===========================
   ORDER CARD
=========================== */
.order-card {
  background: rgba(255,255,255,0.025);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 16px;
  padding: 20px 22px;
  margin-bottom: 14px;
  transition: all 0.32s ease;
  position: relative;
  overflow: hidden;
}
.order-card::before {
  content: "";
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 3px;
  background: linear-gradient(to bottom, var(--gold-dark), var(--gold));
  border-radius: 3px 0 0 3px;
  opacity: 0;
  transition: opacity var(--transition);
}
.order-card:hover {
  border-color: rgba(255,193,7,0.28);
  box-shadow: 0 0 22px rgba(255,193,7,0.12);
  transform: translateY(-3px);
}
.order-card:hover::before { opacity: 1; }

/* Order top row */
.order-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}
.order-id {
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--gold);
  background: var(--gold-dim);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-pill);
  padding: 4px 14px;
  letter-spacing: 0.5px;
}

/* Status badges */
.status-badge {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 5px 14px;
  border-radius: var(--radius-pill);
  letter-spacing: 0.5px;
  text-transform: uppercase;
}
.status-pending   { background: rgba(255,193,7,0.15);  color: var(--gold);    border: 1px solid rgba(255,193,7,0.3); }
.status-approved  { background: rgba(34,197,94,0.12);  color: #4ade80;        border: 1px solid rgba(34,197,94,0.3); }
.status-completed { background: rgba(59,130,246,0.12); color: #60a5fa;        border: 1px solid rgba(59,130,246,0.3); }
.status-cancelled { background: rgba(239,68,68,0.12);  color: #f87171;        border: 1px solid rgba(239,68,68,0.3); }

/* Order info grid */
.order-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 12px 20px;
  margin-bottom: 16px;
}
.info-item { display: flex; flex-direction: column; gap: 2px; }
.info-item .lbl {
  font-size: 0.7rem;
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.info-item .val {
  font-size: 0.9rem;
  color: #f0f0f0;
  font-weight: 500;
}
.info-item .val.gold { color: var(--gold); font-weight: 700; }

/* Actions */
.order-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  padding-top: 14px;
  border-top: 1px solid rgba(255,255,255,0.05);
}
.btn-view {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000;
  font-weight: 700;
  font-size: 0.8rem;
  padding: 8px 18px;
  border-radius: var(--radius-pill);
  border: none;
  cursor: pointer;
  transition: all var(--transition);
  box-shadow: 0 0 12px rgba(255,193,7,0.18);
}
.btn-view:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 20px rgba(255,193,7,0.38);
  color: #000;
}
.btn-track {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  color: var(--gold);
  font-weight: 600;
  font-size: 0.8rem;
  padding: 7px 18px;
  border-radius: var(--radius-pill);
  border: 1px solid rgba(255,193,7,0.35);
  cursor: pointer;
  transition: all var(--transition);
}
.btn-track:hover {
  background: var(--gold-dim);
  border-color: rgba(255,193,7,0.6);
  transform: translateY(-2px);
  color: var(--gold);
}

/* Empty state */
.empty-state {
  text-align: center;
  padding: 60px 20px;
}
.empty-icon {
  width: 80px; height: 80px;
  background: var(--gold-dim);
  border: 1px solid var(--gold-border);
  border-radius: 24px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 20px;
}
.empty-icon i { font-size: 2rem; color: var(--gold); opacity: 0.5; }
.empty-state h5 { font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: 8px; }
.empty-state p  { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px; }

/* Back button */
.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  color: var(--gold);
  font-weight: 600;
  font-size: 0.88rem;
  padding: 10px 24px;
  border-radius: var(--radius-pill);
  border: 1px solid rgba(255,193,7,0.35);
  cursor: pointer;
  transition: all var(--transition);
}
.btn-back:hover {
  background: var(--gold-dim);
  border-color: rgba(255,193,7,0.6);
  transform: translateX(-3px);
  color: var(--gold);
}

/* ===========================
   FOOTER
=========================== */
.footer-section {
  background: #000;
  padding: 26px 0;
  border-top: 1px solid var(--gold-border);
  text-align: center;
}
.footer-copy { font-size: 0.78rem; color: #444; margin: 0; }
.footer-sub  { font-size: 0.74rem; color: #333; margin: 4px 0 0; }

/* ===========================
   ANIMATIONS
=========================== */
.fade-in    { animation: fadeIn 0.85s ease both; }
.fade-in-d1 { animation: fadeIn 0.85s 0.1s ease both; }
.fade-in-d2 { animation: fadeIn 0.85s 0.2s ease both; }

@keyframes fadeIn {
  from { opacity:0; transform:translateY(18px); }
  to   { opacity:1; transform:translateY(0); }
}

/* ===========================
   MOBILE
=========================== */
@media (max-width:767.98px) {
  .orders-page { padding: 100px 0 60px; }
  .glass-wrap  { padding: 22px 16px; }
  .order-card  { padding: 16px; }
  .order-info  { grid-template-columns: 1fr 1fr; }
}
@media (max-width:480px) {
  .order-info  { grid-template-columns: 1fr; }
  .stats-bar   { gap: 8px; }
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

<!-- ORDERS PAGE -->
<main class="orders-page">
  <div class="container">

    <!-- Page Header -->
    <div class="page-header fade-in">
      <div class="header-icon-wrap">
        <i class="bi bi-bag-check-fill"></i>
      </div>
      <p class="page-label">Account</p>
      <h1>My Orders</h1>
      <p>Track and manage all your purchases</p>
    </div>

    <?php
      // Count stats
      $total_orders   = mysqli_num_rows($res);
      $pending = $approved = $completed = 0;
      $rows = [];
      while($row = mysqli_fetch_assoc($res)){
        $rows[] = $row;
        if($row['status']=="pending")   $pending++;
        if($row['status']=="approved")  $approved++;
        if($row['status']=="completed") $completed++;
      }
    ?>

    <!-- Stats Pills -->
    <?php if($total_orders > 0): ?>
    <div class="stats-bar fade-in">
      <div class="stat-pill"><i class="bi bi-receipt" style="color:var(--gold)"></i> Total <strong><?= $total_orders ?></strong></div>
      <?php if($pending):   ?><div class="stat-pill"><i class="bi bi-hourglass-split" style="color:#ffc107"></i> Pending <strong><?= $pending ?></strong></div><?php endif; ?>
      <?php if($approved):  ?><div class="stat-pill"><i class="bi bi-check-circle" style="color:#4ade80"></i> Approved <strong><?= $approved ?></strong></div><?php endif; ?>
      <?php if($completed): ?><div class="stat-pill"><i class="bi bi-bag-check" style="color:#60a5fa"></i> Completed <strong><?= $completed ?></strong></div><?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Orders List -->
    <div class="fade-in-d1">
      <div class="glass-wrap">
        <div class="wrap-title">
          <i class="bi bi-boxes"></i> All Orders
        </div>
        <hr class="wrap-divider">

        <?php if(count($rows) === 0): ?>
          <!-- Empty State -->
          <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-bag-x"></i></div>
            <h5>No orders yet</h5>
            <p>You haven't placed any orders. Start shopping!</p>
            <a href="Products.php" class="btn-view" style="display:inline-flex;">
              <i class="bi bi-shop"></i> Browse Products
            </a>
          </div>

        <?php else: ?>
          <?php foreach($rows as $row): ?>
            <?php
              $statusClass = "status-" . strtolower($row['status']);
              // fallback for unknown statuses
              $knownStatuses = ['pending','approved','completed','cancelled'];
              if(!in_array(strtolower($row['status']), $knownStatuses)) $statusClass = "status-cancelled";
            ?>
            <div class="order-card">
              <!-- Top Row -->
              <div class="order-top">
                <span class="order-id">#ORD<?= $row['id'] ?></span>
                <span class="status-badge <?= $statusClass ?>">
                  <?= ucfirst($row['status']) ?>
                </span>
              </div>

              <!-- Info Grid -->
              <div class="order-info">
                <div class="info-item">
                  <span class="lbl">Product</span>
                  <span class="val"><?= htmlspecialchars($row['product']) ?></span>
                </div>
                <div class="info-item">
                  <span class="lbl">Total</span>
                  <span class="val gold">Rs. <?= number_format($row['total']) ?></span>
                </div>
                <div class="info-item">
                  <span class="lbl">Date</span>
                  <span class="val"><?= date("d M Y", strtotime($row['created_at'])) ?></span>
                </div>
                <div class="info-item">
                  <span class="lbl">Payment</span>
                  <span class="val"><?= ucfirst($row['payment'] ?? 'COD') ?></span>
                </div>
              </div>

              <!-- Actions -->
              <div class="order-actions">
                <a href="orderDetail.php?id=<?= $row['id'] ?>" class="btn-view">
                  <i class="bi bi-eye"></i> View Details
                </a>
                <a href="track.php?id=<?= $row['id'] ?>" class="btn-track">
                  <i class="bi bi-truck"></i> Track Order
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <!-- Back to Profile -->
        <div class="text-center mt-4">
          <a href="profile.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Profile
          </a>
        </div>

      </div>
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
/* ===========================
   FIREWORKS — identical to Index.php
=========================== */
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