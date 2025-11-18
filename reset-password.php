<?php
session_start();
include 'supabase.php';

$token = $_GET['token'] ?? '';

$stmt = $conn->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("Invalid or expired token.");
}

$data = $result->fetch_assoc();
$user_id = $data['user_id'];
$expires_at = strtotime($data['expires_at']);

if (time() > $expires_at) {
  die("This reset link has expired.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password | MindCare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    /* 🌿 Light mode background */
    body {
      background: linear-gradient(to bottom right, #d0f0c0, #a8e6cf);
      color: #1e1e1e;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      transition: background 0.5s ease, color 0.5s ease;
    }

    /* 🌑 Dark mode background */
    body.dark-mode {
      background: linear-gradient(to bottom right, #2e7d32, #1b5e20);
      color: #f8f9fa;
    }

    /* Card design */
    .card {
      width: 100%;
      max-width: 420px;
      background-color: rgba(255, 255, 255, 0.95);
      border-radius: 1rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
      padding: 2rem;
      transition: background-color 0.4s ease, color 0.4s ease;
      color: #1e1e1e;
    }

    body.dark-mode .card {
      background-color: rgba(50, 50, 50, 0.9);
      color: #f8f9fa;
    }

    h4 {
      text-align: center;
      font-weight: bold;
      margin-bottom: 1rem;
    }

    /* Password field with toggle icon */
    .password-wrapper {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #666;
    }

    body.dark-mode .toggle-password {
      color: #ddd;
    }

    /* Buttons */
    .btn-success {
      background-color: #4caf50;
      border: none;
      transition: 0.3s;
    }

    .btn-success:hover {
      background-color: #45a049;
    }

    body.dark-mode .btn-success {
      background-color: #81c784;
      color: #1e1e1e;
    }

    /* 🌗 Dark mode toggle */
    #themeToggle {
      position: absolute;
      top: 20px;
      right: 20px;
      background-color: transparent;
      border: 2px solid #4caf50;
      color: #4caf50;
      border-radius: 25px;
      padding: 8px 14px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
    }

    #themeToggle:hover {
      background-color: #4caf50;
      color: #fff;
    }

    .rotate {
      transform: rotate(360deg);
      transition: transform 0.5s ease;
    }
  </style>
</head>
<body>
  <!-- 🌗 Dark Mode Toggle -->
  <button id="themeToggle">
    <span id="themeIcon">🌞</span>
    <span id="themeLabel">Light Mode</span>
  </button>

  <div class="card">
    <h4>Set New Password</h4>

    <form method="POST" action="update-password.php">
      <input type="hidden" name="user_id" value="<?= $user_id ?>">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

      <!-- New Password -->
      <div class="mb-3 password-wrapper">
        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="New Password" required>
        <span class="toggle-password" onclick="togglePassword('new_password', this)">
          <i class="fa-solid fa-eye"></i>
        </span>
      </div>

      <!-- Confirm Password -->
      <div class="mb-3 password-wrapper">
        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm Password" required>
        <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
          <i class="fa-solid fa-eye"></i>
        </span>
      </div>

      <button type="submit" class="btn btn-success w-100">Update Password</button>
    </form>
  </div>

  <script>
    // 👁️ Toggle password visibility
    function togglePassword(fieldId, iconSpan) {
      const field = document.getElementById(fieldId);
      const icon = iconSpan.querySelector("i");
      if (field.type === "password") {
        field.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
      } else {
        field.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
      }
    }

    // 🌗 Dark mode toggle
    const toggleBtn = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const label = document.getElementById('themeLabel');

    const prefersDark = localStorage.getItem('dark-mode') === 'true';
    if (prefersDark) {
      document.body.classList.add('dark-mode');
      icon.textContent = '🌙';
      label.textContent = 'Dark Mode';
    }

    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      const isDark = document.body.classList.contains('dark-mode');
      localStorage.setItem('dark-mode', isDark);
      icon.classList.add('rotate');
      setTimeout(() => icon.classList.remove('rotate'), 500);
      icon.textContent = isDark ? '🌙' : '🌞';
      label.textContent = isDark ? 'Dark Mode' : 'Light Mode';
    });
  </script>
</body>
</html>
