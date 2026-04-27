<?php session_start(); ?>
<script>let userId = "<?php echo $_SESSION['user_id']; ?>";</script>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hapana Fireworks – Cart</title>
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
  --gold-dim: rgba(255,193,7,0.12);
  --gold-border: rgba(255,193,7,0.18);
  --bg-card: rgba(255,255,255,0.04);
  --text-muted: #c8c8c8;
  --radius-card: 20px;
  --radius-pill: 50px;
  --transition: 0.3s ease;
}

/* ===========================
   RESET & BASE
=========================== */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }

body {
  background: #000;
  color: #fff;
  font-family: 'Poppins', sans-serif;
  overflow-x: hidden;
  min-height: 100vh;
}

a { text-decoration: none; color: inherit; }
img { max-width: 100%; display: block; }

/* ===========================
   FIREWORKS CANVAS
=========================== */
#fireworksCanvas {
  position: fixed;
  inset: 0;
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
}

.logo {
  width: 40px;
  height: 40px;
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

.navbar .nav-link:hover {
  color: var(--gold) !important;
  background: var(--gold-dim);
}

.navbar .nav-link.active {
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000 !important;
  font-weight: 600;
  box-shadow: 0 0 14px rgba(255,193,7,0.3);
}

.navbar-toggler {
  border: 1px solid rgba(255,193,7,0.4);
  padding: 5px 9px;
}
.navbar-toggler:focus { box-shadow: none; }
.navbar-toggler-icon { filter: brightness(10); }

@media (max-width: 991.98px) {
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
   PAGE WRAPPER
=========================== */
.cart-page {
  min-height: 100vh;
  padding: 110px 16px 60px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

/* ===========================
   CART GLASS PANEL
=========================== */
.cart-glass {
  width: 100%;
  max-width: 980px;
  background: var(--bg-card);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-card);
  box-shadow: 0 0 35px rgba(255,193,7,0.1);
  padding: 36px 32px;
}

.cart-glass h2 {
  color: var(--gold);
  font-weight: 700;
  font-size: clamp(1.4rem, 4vw, 2rem);
  text-align: center;
  margin-bottom: 32px;
}

/* ===========================
   TABLE
=========================== */
.cart-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

.cart-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  min-width: 520px;
}

.cart-table thead tr {
  background: rgba(255,193,7,0.07);
}

.cart-table th {
  color: var(--gold);
  font-size: 0.82rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  padding: 12px 14px;
  border-bottom: 1px solid var(--gold-border);
  text-align: center;
  white-space: nowrap;
}

.cart-table td {
  padding: 14px 12px;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  text-align: center;
  font-size: 0.92rem;
  vertical-align: middle;
  color: #e8e8e8;
}

.cart-table tbody tr:last-child td { border-bottom: none; }

.cart-table tbody tr:hover { background: rgba(255,193,7,0.04); }

.product-name-cell {
  text-align: left !important;
  font-weight: 500;
  color: #fff;
  min-width: 140px;
}

/* ===========================
   QTY CONTROLS
=========================== */
.qty-box {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,0.06);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-pill);
  padding: 4px 10px;
}

.qty-btn {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000;
  font-weight: 700;
  font-size: 1rem;
  line-height: 1;
  cursor: pointer;
  transition: all var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.qty-btn:hover { transform: scale(1.15); }

.qty-val {
  min-width: 22px;
  text-align: center;
  font-weight: 600;
  font-size: 0.92rem;
}

/* ===========================
   ACTION BUTTONS
=========================== */
.action-btns { display: flex; align-items: center; justify-content: center; gap: 8px; }

.btn-buy {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(45deg, #1a7a2e, #28a745);
  color: #fff;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-buy:hover { transform: scale(1.12); box-shadow: 0 0 12px rgba(40,167,69,0.5); }

.btn-remove {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: none;
  background: rgba(220,53,69,0.18);
  border: 1px solid rgba(220,53,69,0.35);
  color: #ff6b6b;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-remove:hover {
  background: #dc3545;
  color: #fff;
  transform: scale(1.12);
}

/* ===========================
   EMPTY STATE
=========================== */
.empty-state {
  text-align: center;
  padding: 48px 20px 20px;
  display: none;
}

.empty-state i {
  font-size: 3.5rem;
  color: rgba(255,193,7,0.2);
  display: block;
  margin-bottom: 16px;
}

.empty-state h4 { color: #fff; font-size: 1.1rem; margin-bottom: 8px; }
.empty-state p  { color: #777; font-size: 0.9rem; margin-bottom: 20px; }

.btn-gold-pill {
  display: inline-block;
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000;
  font-weight: 700;
  font-size: 0.9rem;
  padding: 10px 28px;
  border-radius: var(--radius-pill);
  border: none;
  cursor: pointer;
  transition: all var(--transition);
}

.btn-gold-pill:hover { transform: translateY(-2px) scale(1.03); color: #000; box-shadow: 0 0 20px rgba(255,193,7,0.35); }

/* ===========================
   SUMMARY
=========================== */
.cart-summary {
  margin-top: 28px;
  padding: 22px 24px;
  border-radius: 16px;
  background: rgba(255,193,7,0.05);
  border: 1px solid var(--gold-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
}

.summary-total {
  font-size: 1.2rem;
  font-weight: 700;
  color: #fff;
}

.summary-total span { color: var(--gold); font-size: 1.4rem; }

.btn-checkout {
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000;
  font-weight: 700;
  font-size: 0.95rem;
  padding: 12px 32px;
  border-radius: var(--radius-pill);
  border: none;
  cursor: pointer;
  transition: all var(--transition);
  white-space: nowrap;
}

.btn-checkout:hover { transform: translateY(-2px); box-shadow: 0 0 22px rgba(255,193,7,0.4); }
.btn-checkout:active { transform: scale(0.97); }

/* ===========================
   ANIMATION
=========================== */
.fade-in { animation: fadeIn 0.85s ease both; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(18px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ===========================
   RESPONSIVE
=========================== */
@media (max-width: 575.98px) {
  .cart-glass { padding: 24px 16px; }
  .cart-summary { flex-direction: column; text-align: center; }
  .btn-checkout { width: 100%; }
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
      <img src="images/PHOTO-2026-02-12-17-21-53.jpg" class="logo" alt="Hapana Fireworks">
      Hapana Fireworks
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="Index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="Products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link active" href="cart.php">Cart</a></li>
        <li class="nav-item"><a class="nav-link" href="Login.html">Login</a></li>
        <li class="nav-item ms-lg-2">
          <a class="nav-link" href="profile.php" aria-label="Profile">
            <i class="bi bi-person-circle fs-5"></i>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- CART PAGE -->
<div class="cart-page">
  <div class="cart-glass fade-in">
    <h2><i class="bi bi-cart3 me-2"></i>Your Shopping Cart</h2>

    <!-- EMPTY STATE -->
    <div class="empty-state" id="emptyCart">
      <i class="bi bi-cart-x"></i>
      <h4>Your cart is empty</h4>
      <p>Looks like you haven't added anything yet.</p>
      <a href="Products.php" class="btn-gold-pill">Continue Shopping</a>
    </div>

    <!-- TABLE -->
    <div class="cart-table-wrap" id="cartTableWrap">
      <table class="cart-table">
        <thead>
          <tr>
            <th style="text-align:left;">Product</th>
            <th>Unit Price</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="cartBody"></tbody>
      </table>
    </div>

    <!-- SUMMARY -->
    <div class="cart-summary" id="cartSummary">
      <div class="summary-total">
        Grand Total: LKR <span id="grandTotal">0.00</span>
      </div>
      <button class="btn-checkout" onclick="checkoutAll()">
        <i class="bi bi-lightning-charge me-1"></i> Proceed to Checkout
      </button>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Fireworks ─────────────────────────────────────────
const canvas = document.getElementById("fireworksCanvas");
const ctx    = canvas.getContext("2d");

function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
resize();
window.addEventListener("resize", resize);

const fireworks = [], particles = [];

class Firework {
  constructor() {
    this.x = Math.random() * canvas.width;
    this.y = canvas.height;
    this.targetY = Math.random() * canvas.height * 0.5;
    this.color = `hsl(${Math.random()*360},100%,60%)`;
    this.speed = 5; this.exploded = false;
  }
  update() {
    if (!this.exploded) {
      this.y -= this.speed;
      if (this.y <= this.targetY) {
        this.exploded = true;
        for (let i = 0; i < 50; i++) particles.push(new Particle(this.x, this.y, this.color));
      }
    }
  }
  draw() { if (!this.exploded) { ctx.fillStyle = this.color; ctx.fillRect(this.x, this.y, 3, 8); } }
}

class Particle {
  constructor(x, y, color) {
    this.x = x; this.y = y; this.color = color;
    this.radius = 2;
    this.speedX = (Math.random() - 0.5) * 6;
    this.speedY = (Math.random() - 0.5) * 6;
    this.gravity = 0.05; this.alpha = 1;
  }
  update() { this.x += this.speedX; this.y += this.speedY; this.speedY += this.gravity; this.alpha -= 0.01; }
  draw() { ctx.globalAlpha = this.alpha; ctx.fillStyle = this.color; ctx.beginPath(); ctx.arc(this.x, this.y, this.radius, 0, Math.PI*2); ctx.fill(); ctx.globalAlpha = 1; }
}

function animate() {
  ctx.fillStyle = "rgba(0,0,0,0.2)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  if (Math.random() < 0.04) fireworks.push(new Firework());
  for (let i = fireworks.length - 1; i >= 0; i--) { fireworks[i].update(); fireworks[i].draw(); if (fireworks[i].exploded) fireworks.splice(i, 1); }
  for (let i = particles.length - 1; i >= 0; i--) { particles[i].update(); particles[i].draw(); if (particles[i].alpha <= 0) particles.splice(i, 1); }
  requestAnimationFrame(animate);
}
animate();

// ── Cart Logic ────────────────────────────────────────
function getCart() {
  return JSON.parse(localStorage.getItem("cart_" + userId)) || [];
}

function saveCart(cart) {
  localStorage.setItem("cart_" + userId, JSON.stringify(cart));
}

function loadCart() {
  const cart = getCart();
  const tbody   = document.getElementById("cartBody");
  const empty   = document.getElementById("emptyCart");
  const wrap    = document.getElementById("cartTableWrap");
  const summary = document.getElementById("cartSummary");

  tbody.innerHTML = "";
  let grandTotal = 0;

  if (cart.length === 0) {
    empty.style.display   = "block";
    wrap.style.display    = "none";
    summary.style.display = "none";
    return;
  }

  empty.style.display   = "none";
  wrap.style.display    = "block";
  summary.style.display = "flex";

  cart.forEach((item, i) => {
    const total = item.price * item.quantity;
    grandTotal += total;

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td class="product-name-cell">${item.name}</td>
      <td>LKR ${item.price.toLocaleString()}</td>
      <td>
        <div class="qty-box">
          <button class="qty-btn" onclick="changeQty(${i}, -1)">−</button>
          <span class="qty-val">${item.quantity}</span>
          <button class="qty-btn" onclick="changeQty(${i}, 1)">+</button>
        </div>
      </td>
      <td>LKR ${total.toLocaleString()}</td>
      <td>
        <div class="action-btns">
          <button class="btn-buy" onclick="buySingle(${i})" title="Buy now">
            <i class="bi bi-lightning-charge"></i>
          </button>
          <button class="btn-remove" onclick="removeItem(${i})" title="Remove">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });

  document.getElementById("grandTotal").textContent = grandTotal.toLocaleString() + ".00";
}

function changeQty(i, delta) {
  const cart = getCart();
  cart[i].quantity += delta;
  if (cart[i].quantity < 1) cart[i].quantity = 1;
  saveCart(cart);
  loadCart();
}

function removeItem(i) {
  const cart = getCart();
  cart.splice(i, 1);
  saveCart(cart);
  loadCart();
}

function buySingle(i) {
  const cart = getCart();
  localStorage.setItem("checkoutItem", JSON.stringify(cart[i]));
  window.location.href = "checkout.html";
}

function checkoutAll() {
  const cart = getCart();
  if (cart.length === 0) return;
  localStorage.setItem("checkoutAll", JSON.stringify(cart));
  window.location.href = "checkout.html";
}

loadCart();
</script>
</body>
</html>