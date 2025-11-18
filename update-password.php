<?php
session_start();
include 'supabase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_POST['user_id'] ?? '';
  $token = $_POST['token'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  // Basic validation
  if (empty($user_id) || empty($token) || empty($new_password) || empty($confirm_password)) {
    die("All fields are required.");
  }

  if ($new_password !== $confirm_password) {
    die("Passwords do not match.");
  }

  // Verify token again for safety
  $stmt = $conn->prepare("SELECT expires_at FROM password_resets WHERE token = ? AND user_id = ?");
  $stmt->bind_param("si", $token, $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 0) {
    die("Invalid or expired token.");
  }

  $data = $result->fetch_assoc();
  if (time() > strtotime($data['expires_at'])) {
    die("This reset link has expired.");
  }

  // Hash and update password
  $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
  $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
  $update->bind_param("si", $hashed_password, $user_id);

  if ($update->execute()) {
    // Delete token after successful reset
    $delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
    $delete->bind_param("s", $token);
    $delete->execute();

    echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
            <h3>Password updated successfully ✅</h3>
            <a href='login.php' class='btn btn-primary mt-3'>Go to Login</a>
          </div>";
  } else {
    die("Something went wrong. Please try again.");
  }
}
?>