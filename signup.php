<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config.php");
include("mail.php");

$formError = "";
$formSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $formError = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $formError = "Password must be at least 6 characters.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $formError = "An account with this email already exists.";
        } else {
            $sql = "INSERT INTO users(name,email,password,role,status)
                    VALUES('$name','$email','$password_hash','customer','active')";
            if (mysqli_query($conn, $sql)) {
                $subject = "Welcome to Hapana Fireworks";
                $message = "<h2>Hello $name 👋</h2>
                            <p>Your account has been successfully created.</p>
                            <p>Enjoy shopping with Hapana Fireworks 🎇</p>";
                sendMail($email, $subject, $message);
                $formSuccess = true;
            } else {
                $formError = "Database error. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hapana Fireworks – Sign Up</title>
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
  --text-muted: #c8c8c8;
  --radius-card: 20px;
  --radius-pill: 50px;
  --transition: 0.3s ease;
}

/* ===========================
   RESET & BASE
=========================== */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
html, body { height: 100%; }

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
  position: relative;
  z-index: 10;
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
   AUTH SECTION
=========================== */
.auth-section {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 110px 16px 60px;
  position: relative;
  z-index: 1;
}

/* ===========================
   AUTH BOX
=========================== */
.auth-box {
  width: 100%;
  max-width: 440px;
  background: rgba(12,12,12,0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-card);
  padding: 44px 36px;
  box-shadow: 0 0 30px rgba(255,193,7,0.08);
  transition: box-shadow 0.4s ease, transform 0.4s ease;
  animation: fadeIn 0.85s ease both;
}

.auth-box:hover {
  box-shadow: 0 0 40px rgba(255,193,7,0.22);
  transform: translateY(-4px);
}

/* Brand top */
.auth-brand {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  margin-bottom: 24px;
}

.auth-brand-logo {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid rgba(255,193,7,0.6);
  box-shadow: 0 0 18px rgba(255,193,7,0.3);
}

.auth-box h2 {
  color: var(--gold);
  font-weight: 700;
  font-size: 1.55rem;
  text-align: center;
  margin-bottom: 0;
  text-shadow: 0 0 16px rgba(255,193,7,0.25);
}

.auth-subtitle {
  text-align: center;
  color: #777;
  font-size: 0.84rem;
  margin-bottom: 26px;
}

/* ===========================
   SERVER ALERTS
=========================== */
.server-alert {
  border-radius: 12px;
  font-size: 0.87rem;
  padding: 12px 16px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.alert-error {
  background: rgba(220,53,69,0.12);
  border: 1px solid rgba(220,53,69,0.35);
  color: #ff8a8a;
}

.alert-success {
  background: rgba(40,167,69,0.12);
  border: 1px solid rgba(40,167,69,0.35);
  color: #6dde8e;
}

/* ===========================
   FORM ELEMENTS
=========================== */
.form-group { margin-bottom: 18px; }

.form-group label {
  display: block;
  font-weight: 500;
  font-size: 0.87rem;
  color: var(--text-muted);
  margin-bottom: 7px;
}

.input-wrap { position: relative; }

.input-wrap .input-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #555;
  font-size: 0.95rem;
  pointer-events: none;
  transition: color var(--transition);
}

.form-input {
  width: 100%;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 12px;
  color: #fff;
  font-family: 'Poppins', sans-serif;
  font-size: 0.91rem;
  padding: 12px 14px 12px 40px;
  outline: none;
  transition: all var(--transition);
}

.form-input::placeholder { color: #555; }

.form-input:focus {
  background: rgba(255,193,7,0.06);
  border-color: rgba(255,193,7,0.45);
  box-shadow: 0 0 0 3px rgba(255,193,7,0.1);
  color: #fff;
}

.input-wrap:focus-within .input-icon { color: var(--gold); }

/* Password toggle */
.pw-toggle {
  position: absolute;
  right: 13px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #555;
  cursor: pointer;
  font-size: 1rem;
  padding: 0;
  transition: color var(--transition);
}
.pw-toggle:hover { color: var(--gold); }

/* Strength bar */
.strength-wrap { margin-top: 8px; }

.strength-bar {
  height: 4px;
  border-radius: 4px;
  background: rgba(255,255,255,0.08);
  overflow: hidden;
}

.strength-fill {
  height: 100%;
  border-radius: 4px;
  width: 0%;
  transition: width 0.35s ease, background 0.35s ease;
}

.strength-label {
  font-size: 0.75rem;
  margin-top: 5px;
  color: #666;
}

/* Field error */
.field-error {
  font-size: 0.77rem;
  color: #ff6b6b;
  margin-top: 5px;
  display: none;
}
.field-error.visible { display: block; }

/* ===========================
   SUBMIT BUTTON
=========================== */
.btn-signup {
  width: 100%;
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000;
  font-weight: 700;
  font-size: 0.95rem;
  padding: 13px;
  border-radius: var(--radius-pill);
  border: none;
  cursor: pointer;
  transition: all var(--transition);
  margin-top: 6px;
  letter-spacing: 0.3px;
}

.btn-signup:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 22px rgba(255,193,7,0.4);
}

.btn-signup:active { transform: scale(0.98); }

/* ===========================
   FOOTER LINKS
=========================== */
.auth-divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 22px 0 0;
  color: #444;
  font-size: 0.78rem;
}

.auth-divider::before,
.auth-divider::after {
  content: "";
  flex: 1;
  height: 1px;
  background: rgba(255,255,255,0.08);
}

.auth-footer-links {
  text-align: center;
  margin-top: 16px;
  font-size: 0.85rem;
  color: #777;
}

.auth-footer-links a {
  color: var(--gold);
  font-weight: 500;
  transition: color var(--transition);
}
.auth-footer-links a:hover { color: #fff; }

/* ===========================
   SUCCESS STATE
=========================== */
.success-state {
  text-align: center;
  padding: 10px 0 20px;
}

.success-state i {
  font-size: 3.5rem;
  color: #28a745;
  display: block;
  margin-bottom: 16px;
  animation: popIn 0.5s ease;
}

.success-state h3 { color: #fff; font-size: 1.2rem; margin-bottom: 8px; }
.success-state p  { color: #888; font-size: 0.88rem; margin-bottom: 24px; }

@keyframes popIn {
  from { transform: scale(0.4); opacity: 0; }
  to   { transform: scale(1);   opacity: 1; }
}

/* ===========================
   ANIMATION
=========================== */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ===========================
   RESPONSIVE
=========================== */
@media (max-width: 480px) {
  .auth-box { padding: 30px 20px; }
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
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
        <li class="nav-item"><a class="nav-link" href="Login.html">Login</a></li>
        <li class="nav-item"><a class="nav-link active" href="signup.php">Sign Up</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- AUTH SECTION -->
<section class="auth-section">
  <div class="auth-box">

    <?php if ($formSuccess): ?>
      <!-- SUCCESS STATE -->
      <div class="auth-brand">
        <img src="images/PHOTO-2026-02-12-17-21-53.jpg" class="auth-brand-logo" alt="Hapana Fireworks">
      </div>
      <div class="success-state">
        <i class="bi bi-check-circle-fill"></i>
        <h3>Account Created!</h3>
        <p>Welcome to Hapana Fireworks. A confirmation email has been sent to your inbox.</p>
        <a href="Login.html" class="btn-signup" style="display:inline-block; text-decoration:none; padding:13px 40px; width:auto;">
          Go to Login
        </a>
      </div>

    <?php else: ?>

      <!-- BRAND -->
      <div class="auth-brand">
        <img src="images/PHOTO-2026-02-12-17-21-53.jpg" class="auth-brand-logo" alt="Hapana Fireworks">
        <h2>Create Account</h2>
      </div>
      <p class="auth-subtitle">Join Hapana Fireworks and light up every occasion</p>

      <!-- SERVER ERROR -->
      <?php if ($formError): ?>
        <div class="server-alert alert-error">
          <i class="bi bi-exclamation-circle-fill"></i>
          <?php echo htmlspecialchars($formError); ?>
        </div>
      <?php endif; ?>

      <!-- FORM -->
      <form method="POST" action="" id="signupForm" novalidate>

        <!-- Name -->
        <div class="form-group">
          <label for="name">Full Name</label>
          <div class="input-wrap">
            <input type="text" id="name" name="name" class="form-input"
                   placeholder="Your full name" autocomplete="name"
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            <i class="bi bi-person input-icon"></i>
          </div>
          <span class="field-error" id="nameError">Please enter your full name.</span>
        </div>

        <!-- Email -->
        <div class="form-group">
          <label for="email">Email Address</label>
          <div class="input-wrap">
            <input type="email" id="email" name="email" class="form-input"
                   placeholder="you@example.com" autocomplete="email"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            <i class="bi bi-envelope input-icon"></i>
          </div>
          <span class="field-error" id="emailError">Please enter a valid email address.</span>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrap">
            <input type="password" id="password" name="password" class="form-input"
                   placeholder="At least 6 characters" autocomplete="new-password">
            <i class="bi bi-lock input-icon"></i>
            <button type="button" class="pw-toggle" data-target="password" aria-label="Toggle password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div class="strength-wrap">
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
            <p class="strength-label" id="strengthLabel"></p>
          </div>
          <span class="field-error" id="passwordError">Password must be at least 6 characters.</span>
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <div class="input-wrap">
            <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                   placeholder="Repeat your password" autocomplete="new-password">
            <i class="bi bi-lock-fill input-icon"></i>
            <button type="button" class="pw-toggle" data-target="confirm_password" aria-label="Toggle confirm password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <span class="field-error" id="confirmError">Passwords do not match.</span>
        </div>

        <button type="submit" class="btn-signup">Create Account</button>

      </form>

      <div class="auth-divider">or</div>
      <div class="auth-footer-links">
        Already have an account? <a href="Login.html">Sign in</a>
      </div>

    <?php endif; ?>
  </div>
</section>

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
    this.x = Math.random() * canvas.width; this.y = canvas.height;
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
    this.x = x; this.y = y; this.color = color; this.radius = 2;
    this.speedX = (Math.random()-0.5)*6; this.speedY = (Math.random()-0.5)*6;
    this.gravity = 0.05; this.alpha = 1;
  }
  update() { this.x += this.speedX; this.y += this.speedY; this.speedY += this.gravity; this.alpha -= 0.01; }
  draw() { ctx.globalAlpha = this.alpha; ctx.fillStyle = this.color; ctx.beginPath(); ctx.arc(this.x, this.y, this.radius, 0, Math.PI*2); ctx.fill(); ctx.globalAlpha = 1; }
}

function animate() {
  ctx.fillStyle = "rgba(0,0,0,0.2)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  if (Math.random() < 0.04) fireworks.push(new Firework());
  for (let i = fireworks.length-1; i >= 0; i--) { fireworks[i].update(); fireworks[i].draw(); if (fireworks[i].exploded) fireworks.splice(i, 1); }
  for (let i = particles.length-1; i >= 0; i--) { particles[i].update(); particles[i].draw(); if (particles[i].alpha <= 0) particles.splice(i, 1); }
  requestAnimationFrame(animate);
}
animate();

// ── Password toggles ──────────────────────────────────
document.querySelectorAll(".pw-toggle").forEach(btn => {
  btn.addEventListener("click", function () {
    const input = document.getElementById(this.dataset.target);
    const icon  = this.querySelector("i");
    if (input.type === "password") {
      input.type = "text";
      icon.className = "bi bi-eye-slash";
    } else {
      input.type = "password";
      icon.className = "bi bi-eye";
    }
  });
});

// ── Password strength ─────────────────────────────────
const pwInput    = document.getElementById("password");
const fillEl     = document.getElementById("strengthFill");
const labelEl    = document.getElementById("strengthLabel");

if (pwInput) {
  pwInput.addEventListener("input", function () {
    const val = this.value;
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
      { pct: "0%",   color: "transparent", label: "" },
      { pct: "25%",  color: "#e74c3c",     label: "Weak" },
      { pct: "50%",  color: "#e67e22",     label: "Fair" },
      { pct: "75%",  color: "#f1c40f",     label: "Good" },
      { pct: "100%", color: "#2ecc71",     label: "Strong" },
    ];

    const lvl = val.length === 0 ? levels[0] : levels[Math.min(score, 4)];
    fillEl.style.width      = lvl.pct;
    fillEl.style.background = lvl.color;
    labelEl.textContent     = lvl.label;
    labelEl.style.color     = lvl.color;
  });
}

// ── Client-side validation ────────────────────────────
const form = document.getElementById("signupForm");
if (form) {
  form.addEventListener("submit", function (e) {
    let valid = true;

    const name     = document.getElementById("name");
    const email    = document.getElementById("email");
    const password = document.getElementById("password");
    const confirm  = document.getElementById("confirm_password");

    const fields = { name, email, password, confirm_password: confirm };

    // Reset all
    Object.values(fields).forEach(f => { f.style.borderColor = ""; });
    document.querySelectorAll(".field-error").forEach(el => el.classList.remove("visible"));

    function fail(input, errId) {
      input.style.borderColor = "rgba(255,107,107,0.55)";
      document.getElementById(errId).classList.add("visible");
      valid = false;
    }

    if (!name.value.trim()) fail(name, "nameError");

    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRe.test(email.value.trim())) fail(email, "emailError");

    if (password.value.length < 6) fail(password, "passwordError");

    if (confirm.value !== password.value) fail(confirm, "confirmError");

    if (!valid) e.preventDefault();
  });

  // Clear error on input
  ["name","email","password","confirm_password"].forEach(id => {
    const el = document.getElementById(id);
    const errId = id === "confirm_password" ? "confirmError" : id + "Error";
    if (el) el.addEventListener("input", function () {
      this.style.borderColor = "";
      const err = document.getElementById(errId);
      if (err) err.classList.remove("visible");
    });
  });
}
</script>
</body>
</html>