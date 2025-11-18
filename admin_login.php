<?php
session_start();
include 'supabase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  // Validate input
  if (empty($email) || empty($password)) {
    $error = "Email and password are required.";
  } else {
    // Debug: Log the email being searched
    error_log("Admin login attempt for email: " . $email);

    // Get user by email using Supabase REST API with RLS bypass
    $users = supabaseSelect('users', ['email' => $email, 'role' => 'Specialist'], '*', null, null, true);

    // Debug: Log the response
    error_log("Supabase response count: " . count($users));

    if (empty($users)) {
      error_log("No admin/specialist found for email: " . $email);
      $error = "Admin account not found.";
    } else {
      $user = $users[0];

      // Verify password using bcrypt
      if (!password_verify($password, $user['password'])) {
        error_log("Incorrect password for email: " . $email);
        $error = "Incorrect password.";
      } else {
        // Set session data
        $_SESSION['user'] = [
          'id' => $user['id'],
          'fullname' => $user['fullname'],
          'email' => $user['email'],
          'role' => $user['role']
        ];

        error_log("Admin login successful for user ID: " . $user['id']);
        
        // Redirect to specialist dashboard
        header("Location: specialist_dashboard.php");
        exit;
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
  <title>Admin Login - MindCare</title>
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

    .login-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      padding: 40px;
      max-width: 450px;
      width: 100%;
    }

    .login-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .login-header h3 {
      color: var(--text-dark);
      font-weight: 600;
      margin-bottom: 10px;
    }

    .login-header p {
      color: var(--text-muted);
      font-size: 14px;
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
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-header">
      <h3><i class="fas fa-user-shield"></i> Admin Login</h3>
      <p>Access specialist dashboard</p>
    </div>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-envelope"></i></span>
          <input type="email" name="email" class="form-control" placeholder="admin@mindcare.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
          <span class="input-group-text password-toggle" onclick="togglePassword()">
            <i class="fas fa-eye" id="toggleIcon"></i>
          </span>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100">
        <i class="fas fa-sign-in-alt"></i> Login
      </button>
    </form>

    <div class="back-link">
      <a href="login.php"><i class="fas fa-arrow-left"></i> Back to patient login</a>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');
      
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