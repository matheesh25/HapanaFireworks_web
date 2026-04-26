<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hapana Fireworks</title>
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
  --gold-dim: rgba(255,193,7,0.15);
  --gold-border: rgba(255,193,7,0.18);
  --bg-black: #000;
  --bg-dark: #0d0d0d;
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
*, *::before, *::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html { scroll-behavior: smooth; }

body {
  background: var(--bg-black);
  color: var(--text-main);
  font-family: 'Poppins', sans-serif;
  overflow-x: hidden;
  line-height: 1.65;
}

a { text-decoration: none; color: inherit; }

img { max-width: 100%; display: block; }

/* ===========================
   FIREWORKS CANVAS
=========================== */
#fireworksCanvas {
  position: fixed;
  inset: 0;
  width: 100%;
  height: 100%;
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
  transition: var(--transition);
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

/* Mobile nav collapse */
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
   HERO
=========================== */
#hero {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  position: relative;
  padding: 120px 16px 80px;
}

#hero::before {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0.65));
  z-index: 0;
}

#hero > * { position: relative; z-index: 1; }

.hero-inner { max-width: 820px; margin: 0 auto; }

.hero-subtitle {
  color: #ffd86b;
  font-size: 0.85rem;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  margin-bottom: 14px;
  font-weight: 600;
}

#hero h1 {
  font-size: clamp(2rem, 7vw, 4.6rem);
  font-weight: 800;
  color: #fff;
  margin-bottom: 18px;
  text-shadow: 0 0 20px rgba(255,193,7,0.3);
  line-height: 1.15;
}

#hero .lead {
  color: #d0d0d0;
  font-size: clamp(0.95rem, 2.5vw, 1.1rem);
  max-width: 680px;
  margin: 0 auto 30px;
}

.hero-btn {
  display: inline-block;
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000;
  font-weight: 700;
  font-size: 0.95rem;
  padding: 13px 34px;
  border-radius: var(--radius-pill);
  transition: all var(--transition);
  box-shadow: 0 0 20px rgba(255,193,7,0.22);
  letter-spacing: 0.3px;
}

.hero-btn:hover {
  transform: translateY(-3px) scale(1.03);
  color: #000;
  box-shadow: 0 0 28px rgba(255,193,7,0.45);
}

.hero-btn:active { transform: scale(0.97); }

/* ===========================
   SECTION SHARED
=========================== */
.section-title {
  font-size: clamp(1.6rem, 4vw, 2.2rem);
  font-weight: 700;
  margin-bottom: 14px;
  color: var(--gold);
}

.section-text {
  max-width: 820px;
  margin: 0 auto;
  color: #d4d4d4;
  font-size: 0.97rem;
}

/* ===========================
   ABOUT
=========================== */
#about {
  background: linear-gradient(180deg, #0e0e0e 0%, #161616 100%);
  padding: 80px 0;
  border-top: 1px solid var(--gold-border);
  border-bottom: 1px solid var(--gold-border);
}

.about-box {
  background: var(--bg-card);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-card);
  padding: 44px 36px;
  box-shadow: 0 0 30px rgba(0,0,0,0.3);
  backdrop-filter: blur(6px);
}

@media (max-width: 575.98px) {
  .about-box { padding: 28px 20px; }
}

#about p {
  color: #e0e0e0;
  font-size: 0.97rem;
  margin-bottom: 0;
}

/* ===========================
   EVENTS / FEEDBACK
=========================== */
.feedback-section {
  background: linear-gradient(180deg, #070707 0%, #101010 100%);
  padding: 80px 0;
}

.feedback-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 24px;
  margin-top: 44px;
}

.feedback-card {
  background: var(--bg-card);
  border: 1px solid var(--gold-border);
  padding: 16px;
  border-radius: var(--radius-card);
  text-align: center;
  transition: all 0.35s ease;
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  backdrop-filter: blur(8px);
  display: flex;
  flex-direction: column;
}

.feedback-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 0 26px rgba(255,193,7,0.25);
  border-color: rgba(255,193,7,0.35);
}

.feedback-img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 12px;
  margin-bottom: 16px;
}

.feedback-card p {
  color: #e4e4e4;
  font-size: 0.94rem;
  flex: 1;
  margin-bottom: 12px;
}

.feedback-card h6 {
  font-weight: 600;
  color: var(--gold);
  margin-bottom: 0;
  font-size: 0.88rem;
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

.footer-logo {
  color: var(--gold);
  font-weight: 700;
  font-size: 1.4rem;
  margin-bottom: 14px;
  display: block;
}

.footer-title {
  color: var(--gold);
  margin-bottom: 18px;
  font-weight: 600;
  font-size: 1rem;
}

.footer-text {
  font-size: 0.88rem;
  line-height: 1.8;
  color: #c8c8c8;
}

.footer-divider {
  border-color: var(--gold-border);
  margin: 30px 0 20px;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li { margin-bottom: 9px; }

.footer-links a {
  color: #c8c8c8;
  font-size: 0.88rem;
  transition: all var(--transition);
}

.footer-links a:hover {
  color: var(--gold);
  padding-left: 5px;
}

.social-icons { margin-top: 16px; display: flex; flex-wrap: wrap; gap: 10px; }

.social-icons a {
  width: 38px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
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

.footer-section .btn-warning {
  font-weight: 600;
  border: none;
  border-radius: 0 10px 10px 0 !important;
  padding: 0 16px;
  font-size: 0.88rem;
}

.contact-info {
  font-size: 0.85rem;
  color: #c8c8c8;
  line-height: 1.95;
  margin-top: 12px;
}

.footer-copy {
  text-align: center;
  font-size: 0.8rem;
  color: #666;
}

/* ===========================
   ANIMATIONS
=========================== */
.fade-in {
  animation: fadeIn 0.9s ease both;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(18px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ===========================
   MOBILE RESPONSIVE
=========================== */
@media (max-width: 767.98px) {
  #hero { padding: 130px 16px 80px; min-height: 100svh; }
  .feedback-grid { grid-template-columns: 1fr; }
  #about, .feedback-section, .footer-section { padding-top: 60px; padding-bottom: 60px; }
}

@media (max-width: 575.98px) {
  .footer-section { text-align: center; }
  .social-icons { justify-content: center; }
  .contact-info { margin-top: 12px; }
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
        <li class="nav-item"><a class="nav-link active" href="Index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="Products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
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

<!-- HERO -->
<section id="hero" class="fade-in">
  <div class="hero-inner">
    <p class="hero-subtitle">Celebrate Every Moment</p>
    <h1>Hapana Fireworks</h1>
    <p class="lead">Lighting up every celebration with color, safety, excitement, and unforgettable memories for every special occasion.</p>
    <a href="Products.php" class="hero-btn">View Products</a>
  </div>
</section>

<!-- ABOUT -->
<section id="about" class="text-center fade-in">
  <div class="container">
    <div class="about-box">
      <h2 class="section-title">About Us</h2>
      <p class="section-text">
        Hapana Fireworks is a trusted fireworks business located in Negombo, offering a wide range of safe and high-quality fireworks for many kinds of celebrations. We proudly serve customers for festivals, weddings, parties, and special events by providing reliable products at affordable prices while focusing on safety, quality, and customer satisfaction.
      </p>
    </div>
  </div>
</section>

<!-- FEEDBACK / EVENTS -->
<section class="feedback-section fade-in">
  <div class="container text-center">
    <h2 class="section-title">Events &amp; Customer Feedback</h2>
    <p class="section-text">See how Hapana Fireworks brings color, joy, and unforgettable memories to every celebration.</p>
    <div class="feedback-grid">
      <div class="feedback-card">
        <img src="images/event1.jpg" class="feedback-img" alt="New Year Event">
        <p>"Hapana Fireworks made our New Year celebration unforgettable."</p>
        <h6>— New Year Event</h6>
      </div>
      <div class="feedback-card">
        <img src="images/event2.jpg" class="feedback-img" alt="Wedding Ceremony">
        <p>"Best fireworks supplier for wedding events. Highly recommended."</p>
        <h6>— Wedding Ceremony</h6>
      </div>
      <div class="feedback-card">
        <img src="images/event3.jpg" class="feedback-img" alt="Festival Customer">
        <p>"Safe products and excellent customer support. Will order again."</p>
        <h6>— Festival Customer</h6>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer-section">
  <div class="container">
    <div class="row g-4">
      <!-- Company Info -->
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
      <!-- Quick Links -->
      <div class="col-lg-3 col-md-6">
        <h5 class="footer-title">Quick Links</h5>
        <ul class="footer-links">
          <li><a href="Index.php">Home</a></li>
          <li><a href="Products.php">Products</a></li>
          <li><a href="Cart.php">Cart</a></li>
          <li><a href="Login.html">Login</a></li>
        </ul>
      </div>
      <!-- Categories -->
      <div class="col-lg-3 col-md-6">
        <h5 class="footer-title">Categories</h5>
        <ul class="footer-links">
          <li><a href="#">Rockets</a></li>
          <li><a href="#">Crackers</a></li>
          <li><a href="#">Fountains</a></li>
          <li><a href="#">Sparklers</a></li>
        </ul>
      </div>
      <!-- Newsletter -->
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
// Fireworks canvas
const canvas = document.getElementById("fireworksCanvas");
const ctx = canvas.getContext("2d");

function resize() {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
}
resize();
window.addEventListener("resize", resize);

const fireworks = [];
const particles = [];

class Firework {
  constructor() {
    this.x = Math.random() * canvas.width;
    this.y = canvas.height;
    this.targetY = Math.random() * canvas.height * 0.5;
    this.color = `hsl(${Math.random() * 360},100%,60%)`;
    this.speed = 5;
    this.exploded = false;
  }
  update() {
    if (!this.exploded) {
      this.y -= this.speed;
      if (this.y <= this.targetY) {
        this.exploded = true;
        for (let i = 0; i < 50; i++) {
          particles.push(new Particle(this.x, this.y, this.color));
        }
      }
    }
  }
  draw() {
    if (!this.exploded) {
      ctx.fillStyle = this.color;
      ctx.fillRect(this.x, this.y, 3, 8);
    }
  }
}

class Particle {
  constructor(x, y, color) {
    this.x = x;
    this.y = y;
    this.color = color;
    this.radius = 2;
    this.speedX = (Math.random() - 0.5) * 6;
    this.speedY = (Math.random() - 0.5) * 6;
    this.gravity = 0.05;
    this.alpha = 1;
  }
  update() {
    this.x += this.speedX;
    this.y += this.speedY;
    this.speedY += this.gravity;
    this.alpha -= 0.01;
  }
  draw() {
    ctx.globalAlpha = this.alpha;
    ctx.fillStyle = this.color;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
    ctx.fill();
    ctx.globalAlpha = 1;
  }
}

function animate() {
  ctx.fillStyle = "rgba(0,0,0,0.2)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  if (Math.random() < 0.04) fireworks.push(new Firework());

  for (let i = fireworks.length - 1; i >= 0; i--) {
    fireworks[i].update();
    fireworks[i].draw();
    if (fireworks[i].exploded) fireworks.splice(i, 1);
  }

  for (let i = particles.length - 1; i >= 0; i--) {
    particles[i].update();
    particles[i].draw();
    if (particles[i].alpha <= 0) particles.splice(i, 1);
  }

  requestAnimationFrame(animate);
}

animate();
</script>
</body>
</html>