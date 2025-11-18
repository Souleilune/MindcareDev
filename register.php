<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register | MindCare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

    :root{
      --teal-1: #5ad0be;
      --teal-2: #1aa592;
      --teal-3: #0a6a74;
      --line: #e9edf5;
      --field-bg: #f6f7fb;
      --field-text: #2b2f38;
      --muted: #7a828e;
      --btn-from: #38c7a3;
      --btn-to: #2fb29c;
      --bg-white: #ffffff;
      --alert-danger-bg: #ffe6e8;
      --alert-danger-text: #9b1c1f;
      --alert-success-bg: #d4edda;
      --alert-success-text: #155724;
      --info-side-gradient-start: var(--teal-1);
      --info-side-gradient-mid: var(--teal-2);
      --info-side-gradient-end: var(--teal-3);
      --toggle-text-color: #2b2f38;
    }

    body.dark-mode {
      --field-bg: #2a2a2a;
      --field-text: #f1f1f1;
      --muted: #b0b0b0;
      --line: #3a3a3a;
      --bg-white: #1a1a1a;
      --alert-danger-bg: rgba(244, 67, 54, 0.2);
      --alert-danger-text: #ef5350;
      --alert-success-bg: rgba(76, 175, 80, 0.2);
      --alert-success-text: #81c784;
      --info-side-gradient-start: #0a6a74;
      --info-side-gradient-mid: #1aa592;
      --info-side-gradient-end: #2e7d32;
      --toggle-text-color: #5ad0be;
    }

    body {
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color: var(--field-text);
      min-height: 100vh;
      overflow: hidden;
      background: var(--bg-white);
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .theme-toggle-btn {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(0, 0, 0, 0.1);
      color: var(--toggle-text-color);
      padding: 10px 16px;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    body.dark-mode .theme-toggle-btn {
      background: rgba(42, 42, 42, 0.9);
      border-color: rgba(90, 208, 190, 0.3);
    }

    .theme-toggle-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .theme-icon {
      font-size: 18px;
      transition: transform 0.5s ease;
    }

    .theme-icon.rotate {
      transform: rotate(360deg);
    }

    .register-page {
      min-height: 100vh;
      overflow: hidden;
      background: var(--bg-white);
    }
    
    .register-page .row {
      min-height: 100vh;
    }

    .info-side {
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      text-align: left;
      padding: 0 72px;
      color: #fff;
      background:
        radial-gradient(900px 500px at -10% 115%, rgba(255,255,255,.15) 0%, transparent 60%),
        linear-gradient(135deg, var(--info-side-gradient-start) 0%, var(--info-side-gradient-mid) 48%, var(--info-side-gradient-end) 100%);
      border-right: 1px solid var(--line);
      overflow: hidden;
      transition: background 0.5s ease;
    }

    .info-side::before,
    .info-side::after {
      content: '';
      position: absolute;
      bottom: -180px;
      left: -180px;
      border-radius: 50%;
      border: 1px solid rgba(255,255,255,.25);
      pointer-events: none;
    }
    
    .info-side::before {
      width: 520px; 
      height: 520px;
    }
    
    .info-side::after {
      width: 700px; 
      height: 700px;
      border-color: rgba(255,255,255,.15);
    }

    .info-side img {
      height: 200px;
      width: auto;
      margin: 0 0 24px 0;
      transition: opacity 0.3s ease;
    }
    
    .info-side h4 {
      font-size: 40px;
      line-height: 1.1;
      font-weight: 700;
      margin: 8px 0 10px;
      color: #fff;
    }
    
    .info-side p {
      font-size: 16px;
      color: rgba(255,255,255,.9);
      margin-bottom: 18px;
    }
    
    .info-side .text-muted {
      color: rgba(255,255,255,.85) !important;
      font-weight: 500;
    }

    .info-side a.btn-outline-primary {
      background: rgba(255,255,255,.20);
      color: #fff;
      border: none;
      padding: 12px 20px;
      border-radius: 999px;
      font-weight: 600;
      box-shadow: inset 0 0 0 1px rgba(255,255,255,.25);
      transition: transform .15s ease, background .2s ease;
    }
    
    .info-side a.btn-outline-primary:hover {
      background: rgba(255,255,255,.28);
      transform: translateY(-1px);
    }

    .register-form-side {
      background: var(--bg-white);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px;
      transition: background-color 0.3s ease;
    }

    .register-container {
      background: transparent;
      box-shadow: none;
      border-radius: 0;
      width: 420px;
      max-width: 90%;
      padding: 0;
      text-align: left;
    }

    .register-container h3 {
      font-size: 28px;
      font-weight: 700;
      color: var(--field-text);
      margin-bottom: 6px;
      transition: color 0.3s ease;
    }
    
    /* FIX: "Please fill in your details" MUST be visible in dark mode */
    .register-container small {
      color: var(--muted) !important;
      transition: color 0.3s ease;
    }

    .register-container .alert {
      border: none;
      border-radius: 10px;
      margin-bottom: 20px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    
    .register-container .alert-danger {
      background: var(--alert-danger-bg);
      color: var(--alert-danger-text);
    }
    
    .register-container .alert-success {
      background: var(--alert-success-bg);
      color: var(--alert-success-text);
    }

    .register-container input[type="text"],
    .register-container input[type="email"],
    .register-container input[type="password"],
    .register-container input[type="number"],
    .register-container select {
      background-color: var(--field-bg);
      border: none;
      height: 52px;
      border-radius: 999px;
      padding: 12px 18px;
      font-size: 15px;
      color: var(--field-text);
      box-shadow: 0 1px 0 rgba(0,0,0,0.02), 0 8px 24px rgba(18,38,63,0.03);
      transition: all 0.3s ease;
    }

    .register-container input::placeholder {
      color: var(--muted);
      opacity: 0.7;
    }

    .register-container input.fullname-input { padding-left: 52px; }
    .register-container input[type="email"] { padding-left: 52px; }
    .register-container input[type="number"] { padding-left: 52px; }
    .register-container select { padding-left: 52px; }
    .password-wrapper input[type="password"],
    .password-wrapper input[type="text"] { 
      padding-left: 52px; 
      padding-right: 48px; 
    }

    .register-container input.fullname-input {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%2399A3AE' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2'/%3E%3Ccircle cx='12' cy='7' r='4'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: 18px 50%;
    }

    body.dark-mode .register-container input.fullname-input {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23b0b0b0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2'/%3E%3Ccircle cx='12' cy='7' r='4'/%3E%3C/svg%3E");
    }

    .register-container input[type="email"]{
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%2399A3AE' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='5' width='18' height='14' rx='2' ry='2'/%3E%3Cpolyline points='22,7 12,13 2,7'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: 18px 50%;
    }

    body.dark-mode .register-container input[type="email"]{
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23b0b0b0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='5' width='18' height='14' rx='2' ry='2'/%3E%3Cpolyline points='22,7 12,13 2,7'/%3E%3C/svg%3E");
    }

    .register-container select {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%2399A3AE' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'/%3E%3Ccircle cx='9' cy='7' r='4'/%3E%3Cpath d='M23 21v-2a4 4 0 0 0-3-3.87'/%3E%3Cpath d='M16 3.13a4 4 0 0 1 0 7.75'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: 18px 50%;
      appearance: none;
      cursor: pointer;
    }

    body.dark-mode .register-container select {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23b0b0b0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'/%3E%3Ccircle cx='9' cy='7' r='4'/%3E%3Cpath d='M23 21v-2a4 4 0 0 0-3-3.87'/%3E%3Cpath d='M16 3.13a4 4 0 0 1 0 7.75'/%3E%3C/svg%3E");
      color: var(--field-text);
    }

    body.dark-mode .register-container select option {
      background-color: #2a2a2a;
      color: var(--field-text);
    }

    /* Age number icon */
    .register-container input[type="number"] {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%2399A3AE' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: 18px 50%;
    }

    body.dark-mode .register-container input[type="number"] {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23b0b0b0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E");
    }

    .password-wrapper input[type="password"],
    .password-wrapper input[type="text"] {
      background-image: none !important;
    }

    .password-wrapper { 
      position: relative; 
    }
    
    .password-wrapper::before{
      content: "\f023";
      font-family: "Font Awesome 6 Free";
      font-weight: 900;
      font-size: 16px;
      color: #99A3AE;
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      z-index: 1;
      pointer-events: none;
    }

    body.dark-mode .password-wrapper::before {
      color: #b0b0b0;
    }

    .toggle-password {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #99A3AE;
      cursor: pointer;
      z-index: 2;
      transition: color 0.3s ease;
    }

    body.dark-mode .toggle-password {
      color: #b0b0b0;
    }

    .register-container input:focus,
    .register-container select:focus {
      background-color: var(--field-bg);
      box-shadow: 0 0 0 3px rgba(56,199,163,0.18);
      outline: none;
    }

    body.dark-mode .register-container input:focus,
    body.dark-mode .register-container select:focus {
      background-color: #333;
      box-shadow: 0 0 0 3px rgba(90, 208, 190, 0.25);
    }

    .register-container button,
    .register-container .btn-primary {
      background: linear-gradient(135deg, var(--btn-from) 0%, var(--btn-to) 100%);
      border: none;
      height: 56px;
      border-radius: 999px;
      font-weight: 600;
      font-size: 16px;
      letter-spacing: .2px;
      color: #fff;
      width: 100%;
      box-shadow: 0 10px 24px rgba(48,170,153,.35);
      transition: transform .15s ease, box-shadow .2s ease, opacity .2s ease;
    }
    
    .register-container button:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 28px rgba(48,170,153,.42);
    }

    .register-container a {
      color: #7c8a99;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease;
    }
    
    .register-container a:hover { 
      color: #5d6a78; 
      text-decoration: underline; 
    }
    
    .register-container a.text-primary {
      color: var(--teal-2) !important;
      font-weight: 600;
    }

    body.dark-mode .register-container a {
      color: #90caf9 !important;
    }

    body.dark-mode .register-container a:hover {
      color: #64b5f6 !important;
    }

    body.dark-mode .register-container a.text-primary {
      color: var(--teal-1) !important;
    }

    .fade-in {
      animation: fadeInUp .9s ease both;
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(12px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 992px) {
      .info-side { padding: 48px 40px; }
      .info-side img { height: 160px; }
    }
    
    @media (max-width: 768px) {
      .info-side {
        min-height: 44vh;
        border-right: none;
        padding: 40px 24px;
      }
      .register-form-side {
        min-height: 56vh;
        padding: 24px;
      }
      .register-container { width: 100%; max-width: 440px; }
    }
  </style>
</head>

<body class="register-page">
  <button class="theme-toggle-btn" id="themeToggle">
    <span class="theme-icon" id="themeIcon">🌞</span>
    <span id="themeLabel">Light</span>
  </button>

  <div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">

      <div class="col-md-6 info-side">
        <img src="images/MindCare1.png" alt="MindCare Logo" class="img-fluid" id="logoImage" />
        <h4>Join MindCare</h4>
        <p class="text-muted fst-italic">Start your mental wellness journey today.</p>
        <p>Already have an account?</p>
        <a href="login.php" class="btn btn-outline-primary">Sign In Here</a>
      </div>

      <div class="col-md-6 register-form-side">
        <div class="register-container fade-in">
          <h3 class="mb-1">Create Account</h3>
          <small class="d-block mb-4">Please fill in your details</small>

          <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
          <?php elseif (isset($_GET['success'])): ?>
            <div class="alert alert-success">
              Registration successful! You can now <a href="login.php" class="text-primary fw-semibold">log in</a>.
            </div>
          <?php endif; ?>

          <!-- Error container for validation messages -->
          <div id="validationError" style="display: none;"></div>

          <form method="POST" action="register-handler.php" id="registerForm">
            <!-- 1. Full Name -->
            <div class="mb-3">
              <input type="text" name="fullname" class="form-control fullname-input" placeholder="Full Name" required />
            </div>

            <!-- 2. Email -->
            <div class="mb-3">
              <input type="email" name="email" id="email" class="form-control" placeholder="Email Address" required />
            </div>

            <!-- 3. Password -->
            <div class="mb-3 password-wrapper">
              <input type="password" name="password" id="password" class="form-control" placeholder="Password" required />
              <span class="toggle-password" onclick="togglePassword('password', 'toggleIcon')">
                <i id="toggleIcon" class="fa-solid fa-eye"></i>
              </span>
            </div>

            <!-- 4. Confirm Password -->
            <div class="mb-3 password-wrapper">
              <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm Password" required />
              <span class="toggle-password" onclick="togglePassword('confirm_password', 'toggleIconConfirm')">
                <i id="toggleIconConfirm" class="fa-solid fa-eye"></i>
              </span>
            </div>

            <!-- 5. Gender -->
            <div class="mb-3">
              <select name="gender" class="form-select" required>
                <option value="" disabled selected>Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <!-- 6. Age -->
            <div class="mb-3">
              <input type="number" name="age" class="form-control" placeholder="Age" min="1" max="120" required />
            </div>

            <button type="submit" class="btn btn-primary w-100">Create Account</button>
          </form>

          <div class="mt-3 text-center">
            <small>Already have an account?</small>
            <a href="login.php" class="text-primary fw-semibold small">Sign in!</a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script>
    function togglePassword(fieldId, iconId) {
      const passwordField = document.getElementById(fieldId);
      const toggleIcon = document.getElementById(iconId);
      if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
      } else {
        passwordField.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
      }
    }

    // Form validation
    const form = document.getElementById('registerForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const errorContainer = document.getElementById('validationError');

    function showError(message) {
      errorContainer.className = 'alert alert-danger';
      errorContainer.textContent = message;
      errorContainer.style.display = 'block';
      errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function hideError() {
      errorContainer.style.display = 'none';
    }

    form.addEventListener('submit', function(e) {
      hideError();

      // Validate email format
      const email = emailInput.value.trim();
      if (!email.endsWith('@example.com')) {
        e.preventDefault();
        showError('Invalid email format. Email must end with @example.com');
        emailInput.focus();
        return false;
      }

      // Validate password match
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;
      if (password !== confirmPassword) {
        e.preventDefault();
        showError('Passwords do not match. Please ensure both password fields are identical.');
        confirmPasswordInput.focus();
        return false;
      }
    });

    // Real-time validation feedback
    emailInput.addEventListener('blur', function() {
      const email = this.value.trim();
      if (email && !email.endsWith('@example.com')) {
        this.style.border = '2px solid #dc3545';
      } else {
        this.style.border = '';
      }
    });

    emailInput.addEventListener('focus', function() {
      hideError();
    });

    confirmPasswordInput.addEventListener('input', function() {
      const password = passwordInput.value;
      const confirmPassword = this.value;
      
      if (confirmPassword && password !== confirmPassword) {
        this.style.border = '2px solid #dc3545';
      } else {
        this.style.border = '';
      }
    });

    confirmPasswordInput.addEventListener('focus', function() {
      hideError();
    });

    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const themeLabel = document.getElementById('themeLabel');
    const logoImage = document.getElementById('logoImage');

    function updateLogo(isDark) {
      logoImage.src = isDark ? 'images/MindCare.png' : 'images/MindCare.png';
    }

    const prefersDark = localStorage.getItem('dark-mode') === 'true';
    if (prefersDark) {
      document.body.classList.add('dark-mode');
      themeIcon.textContent = '🌙';
      themeLabel.textContent = 'Dark';
      updateLogo(true);
    }

    themeToggle.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      const isDark = document.body.classList.contains('dark-mode');
      localStorage.setItem('dark-mode', isDark);
      
      themeIcon.classList.add('rotate');
      setTimeout(() => themeIcon.classList.remove('rotate'), 500);
      
      themeIcon.textContent = isDark ? '🌙' : '🌞';
      themeLabel.textContent = isDark ? 'Dark' : 'Light';
      updateLogo(isDark);
    });
  </script>
</body>
</html>