<?php
session_start();
include("config.php");

if(!isset($_GET['id']) || empty($_GET['id'])){
    die("Product not found.");
}

$id = intval($_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
if(mysqli_num_rows($query) == 0){
    die("Product not found.");
}

$product  = mysqli_fetch_assoc($query);
$image    = !empty($product['image']) ? "admin/uploads/" . $product['image'] : "https://via.placeholder.com/500x500?text=No+Image";
$userId   = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hapana Fireworks – <?= htmlspecialchars($product['name']) ?></title>
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
.navbar .nav-link.active {
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000 !important;
  font-weight: 600;
  box-shadow: 0 0 14px rgba(255,193,7,0.3);
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
   PRODUCT PAGE
=========================== */
.product-page {
  min-height: 100vh;
  padding: 110px 0 80px;
}

/* Breadcrumb */
.breadcrumb-bar {
  margin-bottom: 28px;
}
.breadcrumb-bar a {
  color: var(--text-muted);
  font-size: 0.82rem;
  transition: var(--transition);
}
.breadcrumb-bar a:hover { color: var(--gold); }
.breadcrumb-bar span {
  color: #444;
  margin: 0 7px;
  font-size: 0.82rem;
}
.breadcrumb-bar .current {
  color: var(--gold);
  font-size: 0.82rem;
  font-weight: 500;
}

/* ===========================
   IMAGE PANEL
=========================== */
.img-panel {
  position: sticky;
  top: 90px;
}

.main-img-wrap {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid var(--gold-border);
  box-shadow: 0 0 40px rgba(255,193,7,0.15);
  background: rgba(255,255,255,0.03);
  aspect-ratio: 1 / 1;
}

.main-img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
  display: block;
}

.main-img-wrap:hover img { transform: scale(1.04); }

.img-badge {
  position: absolute;
  top: 14px;
  left: 14px;
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 5px 12px;
  border-radius: var(--radius-pill);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.img-badge.out {
  background: rgba(239,68,68,0.85);
  color: #fff;
}

/* ===========================
   PRODUCT CARD
=========================== */
.product-card {
  background: var(--bg-card);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-card);
  padding: 36px 32px;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 8px 40px rgba(0,0,0,0.3);
}

.category-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: var(--gold-dim);
  border: 1px solid var(--gold-border);
  color: var(--gold);
  font-size: 0.73rem;
  font-weight: 700;
  padding: 4px 14px;
  border-radius: var(--radius-pill);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 14px;
}

.product-name {
  font-size: clamp(1.4rem, 3.5vw, 2rem);
  font-weight: 800;
  color: #fff;
  line-height: 1.2;
  margin-bottom: 12px;
}

.product-desc {
  color: var(--text-muted);
  font-size: 0.92rem;
  margin-bottom: 0;
}

.gold-divider {
  border: none;
  border-top: 1px solid var(--gold-border);
  margin: 22px 0;
}

/* Price */
.price-block { margin-bottom: 4px; }

.price-label {
  font-size: 0.7rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  margin-bottom: 4px;
}

.price-val {
  font-size: clamp(1.6rem, 4vw, 2.1rem);
  font-weight: 800;
  color: var(--gold);
  line-height: 1;
}

/* Info boxes */
.info-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 22px;
}

.info-box {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px;
  padding: 14px 16px;
}

.info-box-lbl {
  font-size: 0.68rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  margin-bottom: 4px;
}

.info-box-val {
  font-size: 0.92rem;
  font-weight: 600;
  color: #f0f0f0;
}

.info-box-val.in-stock  { color: #4ade80; }
.info-box-val.low-stock { color: var(--gold); }
.info-box-val.no-stock  { color: #f87171; }

/* Quantity */
.qty-section { margin-bottom: 22px; }

.qty-label {
  font-size: 0.72rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  margin-bottom: 10px;
}

.qty-controls {
  display: flex;
  align-items: center;
  gap: 0;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-pill);
  width: fit-content;
  overflow: hidden;
}

.qty-btn {
  background: transparent;
  border: none;
  color: var(--gold);
  width: 42px;
  height: 42px;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.qty-btn:hover {
  background: var(--gold-dim);
  color: var(--gold);
}

.qty-btn:disabled {
  color: #333;
  cursor: not-allowed;
}

.qty-display {
  min-width: 44px;
  text-align: center;
  font-size: 1rem;
  font-weight: 700;
  color: #fff;
  border-left: 1px solid var(--gold-border);
  border-right: 1px solid var(--gold-border);
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  user-select: none;
}

/* CTA Buttons */
.cta-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.btn-add-cart {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000;
  font-weight: 700;
  font-size: 0.9rem;
  padding: 13px 20px;
  border-radius: var(--radius-pill);
  border: none;
  cursor: pointer;
  transition: all var(--transition);
  box-shadow: 0 0 16px rgba(255,193,7,0.22);
}

.btn-add-cart:hover {
  transform: translateY(-3px);
  box-shadow: 0 0 28px rgba(255,193,7,0.45);
  color: #000;
}

.btn-add-cart:active { transform: scale(0.97); }

.btn-buy-now {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: transparent;
  color: var(--gold);
  font-weight: 700;
  font-size: 0.9rem;
  padding: 12px 20px;
  border-radius: var(--radius-pill);
  border: 2px solid rgba(255,193,7,0.45);
  cursor: pointer;
  transition: all var(--transition);
}

.btn-buy-now:hover {
  background: var(--gold-dim);
  border-color: var(--gold);
  transform: translateY(-3px);
  color: var(--gold);
  box-shadow: 0 0 20px rgba(255,193,7,0.2);
}

.btn-buy-now:active { transform: scale(0.97); }

/* Toast */
.toast-wrap {
  position: fixed;
  bottom: 28px;
  right: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.toast-item {
  background: rgba(20,20,20,0.96);
  border: 1px solid var(--gold-border);
  border-radius: 14px;
  padding: 13px 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.87rem;
  font-weight: 500;
  color: #fff;
  box-shadow: 0 8px 24px rgba(0,0,0,0.4);
  animation: toastIn 0.35s ease both, toastOut 0.35s ease 2.6s both;
  backdrop-filter: blur(12px);
}

.toast-item i { color: var(--gold); font-size: 1.1rem; flex-shrink: 0; }

@keyframes toastIn {
  from { opacity: 0; transform: translateY(14px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes toastOut {
  from { opacity: 1; transform: translateY(0); }
  to   { opacity: 0; transform: translateY(14px); }
}

/* ===========================
   FEATURES STRIP
=========================== */
.features-strip {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
  margin-top: 20px;
}

.feature-item {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  padding: 14px;
  text-align: center;
}

.feature-item i {
  font-size: 1.3rem;
  color: var(--gold);
  display: block;
  margin-bottom: 7px;
}

.feature-item p {
  font-size: 0.72rem;
  color: var(--text-muted);
  margin: 0;
  font-weight: 500;
  line-height: 1.35;
}

/* ===========================
   FOOTER
=========================== */
.footer-section {
  background: #000;
  padding: 70px 0 24px;
  color: #c8c8c8;
  border-top: 1px solid var(--gold-border);
}
.footer-logo { color: var(--gold); font-weight: 700; font-size: 1.4rem; margin-bottom: 14px; display: block; }
.footer-title { color: var(--gold); margin-bottom: 18px; font-weight: 600; font-size: 1rem; }
.footer-text  { font-size: 0.88rem; line-height: 1.8; color: #c8c8c8; }
.footer-divider { border-color: var(--gold-border); margin: 30px 0 20px; }

.footer-links { list-style: none; padding: 0; margin: 0; }
.footer-links li { margin-bottom: 9px; }
.footer-links a { color: #c8c8c8; font-size: 0.88rem; transition: all var(--transition); }
.footer-links a:hover { color: var(--gold); padding-left: 5px; }

.social-icons { margin-top: 16px; display: flex; flex-wrap: wrap; gap: 10px; }
.social-icons a {
  width: 38px; height: 38px;
  display: inline-flex; align-items: center; justify-content: center;
  border-radius: 50%;
  color: var(--gold);
  border: 1px solid rgba(255,193,7,0.28);
  font-size: 16px;
  transition: all var(--transition);
  flex-shrink: 0;
}
.social-icons a:hover {
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000;
  transform: translateY(-3px);
}

.footer-input {
  background: #111 !important;
  border: 1px solid #333 !important;
  color: #fff !important;
  border-radius: 10px 0 0 10px !important;
  min-height: 44px;
  font-size: 0.88rem;
}
.footer-input::placeholder { color: #888; }
.footer-section .btn-warning { font-weight: 600; border: none; border-radius: 0 10px 10px 0 !important; padding: 0 16px; font-size: 0.88rem; }

.contact-info { font-size: 0.85rem; color: #c8c8c8; line-height: 1.95; margin-top: 12px; }
.footer-copy  { text-align: center; font-size: 0.8rem; color: #666; }

/* ===========================
   ANIMATIONS
=========================== */
.fade-in    { animation: fadeIn 0.85s ease both; }
.fade-in-d1 { animation: fadeIn 0.85s 0.1s ease both; }
.fade-in-d2 { animation: fadeIn 0.85s 0.18s ease both; }

@keyframes fadeIn {
  from { opacity:0; transform:translateY(18px); }
  to   { opacity:1; transform:translateY(0); }
}

/* ===========================
   MOBILE
=========================== */
@media (max-width:767.98px) {
  .product-page { padding: 100px 0 70px; }
  .product-card { padding: 24px 18px; }
  .img-panel    { position: static; }
  .info-row     { grid-template-columns: 1fr 1fr; }
  .cta-group    { grid-template-columns: 1fr; }
  .features-strip { grid-template-columns: 1fr 1fr 1fr; }
}

@media (max-width:480px) {
  .info-row { grid-template-columns: 1fr; }
  .features-strip { grid-template-columns: 1fr; }
}

@media (max-width:575.98px) {
  .footer-section { text-align: center; }
  .social-icons   { justify-content: center; }
  .footer-links a:hover { padding-left: 0; }
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
        <li class="nav-item"><a class="nav-link active" href="Products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
        <li class="nav-item"><a class="nav-link" href="Login.html">Login</a></li>
        <li class="nav-item ms-lg-2">
          <a class="nav-link" href="profile.php" aria-label="Profile">
            <i class="bi bi-person-circle fs-5" style="color:var(--gold);filter:drop-shadow(0 0 6px rgba(255,193,7,0.55));"></i>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- PRODUCT PAGE -->
<main class="product-page">
  <div class="container">

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar fade-in">
      <a href="Index.php">Home</a>
      <span>/</span>
      <a href="Products.php">Products</a>
      <span>/</span>
      <span class="current"><?= htmlspecialchars($product['name']) ?></span>
    </div>

    <div class="row g-4 align-items-start">

      <!-- IMAGE PANEL -->
      <div class="col-md-6 fade-in">
        <div class="img-panel">
          <div class="main-img-wrap">
            <img id="mainImage" src="<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <?php if($product['stock'] <= 0): ?>
              <span class="img-badge out">Out of Stock</span>
            <?php elseif($product['stock'] <= 5): ?>
              <span class="img-badge">Low Stock</span>
            <?php else: ?>
              <span class="img-badge">In Stock</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- PRODUCT DETAILS -->
      <div class="col-md-6 fade-in-d1">
        <div class="product-card">

          <!-- Category -->
          <div class="category-chip">
            <i class="bi bi-tag-fill"></i>
            <?= htmlspecialchars($product['category']) ?>
          </div>

          <!-- Name -->
          <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>

          <!-- Description -->
          <p class="product-desc">
            Premium quality firework from Hapana Fireworks — safe, vibrant, and perfect for every celebration and special event.
          </p>

          <hr class="gold-divider">

          <!-- Price -->
          <div class="price-block">
            <p class="price-label">Unit Price</p>
            <p class="price-val">LKR <?= number_format($product['price'], 2) ?></p>
          </div>

          <hr class="gold-divider">

          <!-- Info Row -->
          <div class="info-row">
            <div class="info-box">
              <p class="info-box-lbl">Available Stock</p>
              <p class="info-box-val <?= $product['stock'] <= 0 ? 'no-stock' : ($product['stock'] <= 5 ? 'low-stock' : 'in-stock') ?>">
                <?php
                  if($product['stock'] <= 0)     echo '<i class="bi bi-x-circle-fill me-1"></i>Out of Stock';
                  elseif($product['stock'] <= 5) echo '<i class="bi bi-exclamation-circle-fill me-1"></i>' . $product['stock'] . ' left';
                  else                           echo '<i class="bi bi-check-circle-fill me-1"></i>' . $product['stock'] . ' units';
                ?>
              </p>
            </div>
            <div class="info-box">
              <p class="info-box-lbl">Category</p>
              <p class="info-box-val">
                <i class="bi bi-stars me-1" style="color:var(--gold);"></i>
                <?= htmlspecialchars($product['category']) ?>
              </p>
            </div>
          </div>

          <!-- Quantity -->
          <?php if($product['stock'] > 0): ?>
          <div class="qty-section">
            <p class="qty-label">Quantity</p>
            <div class="qty-controls">
              <button class="qty-btn" id="qtyMinus" onclick="changeQty(-1)" aria-label="Decrease quantity">
                <i class="bi bi-dash"></i>
              </button>
              <div class="qty-display" id="quantity">1</div>
              <button class="qty-btn" id="qtyPlus" onclick="changeQty(1)" aria-label="Increase quantity">
                <i class="bi bi-plus"></i>
              </button>
            </div>
          </div>
          <?php endif; ?>

          <!-- CTA Buttons -->
          <div class="cta-group">
            <?php if($product['stock'] > 0): ?>
            <button class="btn-add-cart" onclick="goToCart()">
              <i class="bi bi-cart-plus-fill"></i> Add to Cart
            </button>
            <button class="btn-buy-now" onclick="goToBuyNow()">
              <i class="bi bi-lightning-charge-fill"></i> Buy Now
            </button>
            <?php else: ?>
            <button class="btn-add-cart" disabled style="opacity:0.4;cursor:not-allowed;grid-column:1/-1;">
              <i class="bi bi-x-circle"></i> Out of Stock
            </button>
            <?php endif; ?>
          </div>

          <!-- Features -->
          <div class="features-strip">
            <div class="feature-item">
              <i class="bi bi-shield-check-fill"></i>
              <p>Safety Certified</p>
            </div>
            <div class="feature-item">
              <i class="bi bi-truck"></i>
              <p>Fast Delivery</p>
            </div>
            <div class="feature-item">
              <i class="bi bi-award-fill"></i>
              <p>Premium Quality</p>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</main>

<!-- TOAST CONTAINER -->
<div class="toast-wrap" id="toastWrap"></div>

<!-- FOOTER -->
<footer class="footer-section">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-3 col-md-6">
        <span class="footer-logo">Hapana Fireworks</span>
        <p class="footer-text">Bringing light to every celebration with premium quality fireworks. Safe, colorful and unforgettable moments.</p>
        <div class="social-icons">
          <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
          <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <h5 class="footer-title">Quick Links</h5>
        <ul class="footer-links">
          <li><a href="Index.php">Home</a></li>
          <li><a href="Products.php">Products</a></li>
          <li><a href="Cart.php">Cart</a></li>
          <li><a href="Login.html">Login</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h5 class="footer-title">Categories</h5>
        <ul class="footer-links">
          <li><a href="#">Rockets</a></li>
          <li><a href="#">Crackers</a></li>
          <li><a href="#">Fountains</a></li>
          <li><a href="#">Sparklers</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h5 class="footer-title">Newsletter</h5>
        <p class="footer-text">Subscribe to get special offers &amp; festival discounts.</p>
        <div class="input-group mb-3">
          <input type="email" class="form-control footer-input" placeholder="Enter your email" aria-label="Email">
          <button class="btn btn-warning" type="button">Subscribe</button>
        </div>
        <p class="contact-info">
          📍 Negombo, Sri Lanka<br>
          📞 +94 77 123 4567<br>
          ✉ info@hapanafireworks.com
        </p>
      </div>
    </div>
    <hr class="footer-divider">
    <p class="footer-copy">© 2026 Hapana Fireworks. All rights reserved.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ===========================
   PHP DATA TO JS
=========================== */
const PRODUCT_ID    = <?= (int)$product['id'] ?>;
const PRODUCT_NAME  = <?= json_encode($product['name']) ?>;
const PRODUCT_PRICE = <?= (float)$product['price'] ?>;
const PRODUCT_STOCK = <?= (int)$product['stock'] ?>;
const USER_ID       = <?= $userId ?>;

/* ===========================
   QUANTITY CONTROL
=========================== */
function getQty(){ return parseInt(document.getElementById("quantity").innerText); }

function changeQty(delta){
  const qty   = getQty();
  const next  = qty + delta;
  const minus = document.getElementById("qtyMinus");
  const plus  = document.getElementById("qtyPlus");
  if(next < 1 || next > PRODUCT_STOCK) return;
  document.getElementById("quantity").innerText = next;
  minus.disabled = (next <= 1);
  plus.disabled  = (next >= PRODUCT_STOCK);
}

/* ===========================
   TOAST
=========================== */
function showToast(msg, icon = "check-circle-fill"){
  const wrap = document.getElementById("toastWrap");
  const el   = document.createElement("div");
  el.className = "toast-item";
  el.innerHTML = `<i class="bi bi-${icon}"></i> ${msg}`;
  wrap.appendChild(el);
  setTimeout(() => el.remove(), 3000);
}



/* ===========================
   ADD TO CART → cart.php
=========================== */
function goToCart(){
  const qty = getQty();
  window.location.href = "cart.php?id=" + PRODUCT_ID + "&qty=" + qty;
}

/* ===========================
   BUY NOW → checkout.html
=========================== */
function goToBuyNow(){
  const qty = getQty();
  const item = {
    id:       String(PRODUCT_ID),
    name:     PRODUCT_NAME,
    price:    PRODUCT_PRICE,
    quantity: qty
  };
  localStorage.setItem("checkoutItem", JSON.stringify(item));
  localStorage.removeItem("checkoutAll");
  window.location.href = "checkout.html";
}

/* ===========================
   FIREWORKS
=========================== */
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
        for(let i=0;i<50;i++) particles.push(new Particle(this.x, this.y, this.color));
      }
    }
  }
  draw(){
    if(!this.exploded){ ctx.fillStyle = this.color; ctx.fillRect(this.x, this.y, 3, 8); }
  }
}

class Particle {
  constructor(x,y,color){
    this.x = x; this.y = y; this.color = color; this.radius = 2;
    this.speedX = (Math.random()-0.5)*6;
    this.speedY = (Math.random()-0.5)*6;
    this.gravity = 0.05; this.alpha = 1;
  }
  update(){ this.x+=this.speedX; this.y+=this.speedY; this.speedY+=this.gravity; this.alpha-=0.01; }
  draw(){
    ctx.globalAlpha = this.alpha;
    ctx.fillStyle   = this.color;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.radius, 0, Math.PI*2);
    ctx.fill();
    ctx.globalAlpha = 1;
  }
}

function animate(){
  ctx.fillStyle = "rgba(0,0,0,0.2)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  if(Math.random() < 0.04) fireworks.push(new Firework());
  for(let i=fireworks.length-1;i>=0;i--){
    fireworks[i].update(); fireworks[i].draw();
    if(fireworks[i].exploded) fireworks.splice(i,1);
  }
  for(let i=particles.length-1;i>=0;i--){
    particles[i].update(); particles[i].draw();
    if(particles[i].alpha<=0) particles.splice(i,1);
  }
  requestAnimationFrame(animate);
}
animate();

/* Initial qty button state */
document.addEventListener("DOMContentLoaded", () => {
  const minus = document.getElementById("qtyMinus");
  if(minus){ minus.disabled = true; }
});
</script>
</body>
</html>