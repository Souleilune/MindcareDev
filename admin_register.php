<?php
session_start();
include 'supabase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = trim($_POST['fullname']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);
  $confirm_password = trim($_POST['confirm_password']);
  $role = trim($_POST['role']);

  if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
    $error = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
  } elseif ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  } else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $error = "Email already registered.";
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);

      $insert = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
      $insert->bind_param("ssss", $fullname, $email, $hashed_password, $role);

      if ($insert->execute()) {
        $success = ucfirst($role) . " account created successfully!";
      } else {
        $error = "Something went wrong. Please try again.";
      }

      $insert->close();
    }

    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
  <h3>User Registration</h3>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php elseif (isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <form method="POST">
    <input type="text" name="fullname" class="form-control mb-3" placeholder="Full Name" required>
    <input type="email" name="email" class="form-control mb-3" placeholder="Email Address" required>
    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
    <input type="password" name="confirm_password" class="form-control mb-3" placeholder="Confirm Password" required>

    <select name="role" class="form-control mb-3" required>
      <option value="" disabled selected>Select Role</option>
      <option value="Patient">Patient</option>
      <option value="Specialist">Specialist</option>
    </select>

    <button type="submit" class="btn btn-primary">Register</button>
  </form>

  <p class="mt-3">
    Already have an account? <a href="admin_login.php">Login here</a>.
  </p>
</body>
</html>
