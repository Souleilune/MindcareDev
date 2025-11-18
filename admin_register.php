<?php
session_start();
include 'supabase.php';

// This is a superadmin task - in production, add authentication check here
// For now, we'll allow access but this should be restricted to superadmin only

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = trim($_POST['fullname']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);
  $confirm_password = trim($_POST['confirm_password']);
  
  // Role is fixed to Specialist for this superadmin function
  $role = 'Specialist';

  // Validate input
  if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
    $error = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
  } elseif ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  } elseif (strlen($password) < 6) {
    $error = "Password must be at least 6 characters long.";
  } else {
    // Check if email already exists using Supabase
    $existingUsers = supabaseSelect('users', ['email' => $email], '*', null, null, true);

    if (!empty($existingUsers)) {
      $error = "Email already registered.";
    } else {
      // Hash password using bcrypt
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);

      // Insert new specialist user
      $result = supabaseInsert('users', [
        'fullname' => $fullname,
        'email' => $email,
        'password' => $hashed_password,
        'role' => $role
      ], true);

      if (isset($result['error'])) {
        error_log("Failed to create specialist: " . json_encode($result));
        $error = "Something went wrong. Please try again.";
      } else {
        $success = "Specialist account created successfully!";
        error_log("Specialist account created for: " . $email);
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
  <title>Register Specialist - MindCare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-teal: #5ad0be;
      --primary-teal-dark: #1aa592;
      --text-dark: #2b2f38;
      --text-muted: #7a828e;
    }

    body {
      background: linear-gradient(135deg, var(--primary-teal) 0%, var(--primary-teal-dark) 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 20px;
    }

    .register-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      padding: 40px;
      max-width: 500px;
      width: 100%;
    }

    .register-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .register-header h3 {
      color: var(--text-dark);
      font-weight: 600;
      margin-bottom: 10px;
    }

    .register-header p {
      color: var(--text-muted);
      font-size: 14px;
    }

    .superadmin-badge {
      background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 10px;
    }

    .form-control {
      border-radius: 10px;
      border: 1px solid #e0e0e0;
      padding: 12px 15px;
      font-size: 14px;
      transition: all 0.3s;
    }

    .form-control:focus {
      border-color: var(--primary-teal);
      box-shadow: 0 0 0 0.2rem rgba(90, 208, 190, 0.25);
    }

    .input-group-text {
      background: white;
      border-radius: 10px;
      border: 1px solid #e0e0e0;
      border-right: none;
    }

    .input-group .form-control {
      border-left: none;
    }

    .password-toggle {
      background: white;
      border: 1px solid #e0e0e0;
      border-left: none;
      border-radius: 0 10px 10px 0;
      cursor: pointer;
      color: var(--text-muted);
      transition: color 0.3s;
    }

    .password-toggle:hover {
      color: var(--primary-teal);
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary-teal) 0%, var(--primary-teal-dark) 100%);
      border: none;
      border-radius: 10px;
      padding: 12px;
      font-weight: 600;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(90, 208, 190, 0.3);
    }

    .alert {
      border-radius: 10px;
      border: none;
      font-size: 14px;
    }

    .back-link {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
    }

    .back-link a {
      color: var(--primary-teal-dark);
      text-decoration: none;
      font-weight: 500;
    }

    .back-link a:hover {
      text-decoration: underline;
    }

    .form-label {
      font-weight: 500;
      color: var(--text-dark);
      font-size: 14px;
      margin-bottom: 8px;
    }

    .info-box {
      background: #f8f9fa;
      border-left: 4px solid var(--primary-teal);
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 13px;
      color: var(--text-muted);
    }
  </style>
</head>
<body>
  <div class="register-card">
    <div class="register-header">
      <span class="superadmin-badge"><i class="fas fa-crown"></i> SUPERADMIN</span>
      <h3><i class="fas fa-user-plus"></i> Register Specialist</h3>
      <p>Create new specialist account</p>
    </div>

    <div class="info-box">
      <i class="fas fa-info-circle"></i> This form creates specialist accounts only. All registered users will have Specialist role by default.
    </div>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-user"></i></span>
          <input type="text" name="fullname" class="form-control" placeholder="Dr. John Doe" required value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-envelope"></i></span>
          <input type="email" name="email" class="form-control" placeholder="specialist@mindcare.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" name="password" id="password" class="form-control" placeholder="Minimum 6 characters" required>
          <span class="input-group-text password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
            <i class="fas fa-eye" id="toggleIcon1"></i>
          </span>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter password" required>
          <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
            <i class="fas fa-eye" id="toggleIcon2"></i>
          </span>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100">
        <i class="fas fa-user-plus"></i> Register Specialist
      </button>
    </form>

    <div class="back-link">
      <a href="admin_login.php"><i class="fas fa-arrow-left"></i> Back to admin login</a>
    </div>
  </div>

  <script>
    function togglePassword(inputId, iconId) {
      const passwordInput = document.getElementById(inputId);
      const toggleIcon = document.getElementById(iconId);
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>