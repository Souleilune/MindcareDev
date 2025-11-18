<?php
session_start();
include 'supabase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'Specialist'");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if ($password === $user['password']) {
      $_SESSION['user'] = [
        'id' => $user['id'],
        'fullname' => $user['fullname'],
        'role' => $user['role']
      ];
      header("Location: specialist_dashboard.php");
      exit;
    } else {
      $error = "Incorrect password.";
    }
  } else {
    $error = "Admin account not found.";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
  <h3>Admin Login</h3>
  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>
  <form method="POST">
    <input type="email" name="email" class="form-control mb-3" placeholder="Admin Email" required>
    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
    <button type="submit" class="btn btn-primary">Login</button>
  </form>
</body>
</html>