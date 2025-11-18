<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include 'supabase.php';

$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$gender = $_POST['gender'];  // KEEP THIS - from registration form
$age = intval($_POST['age']); // KEEP THIS - from registration form

// Check if email already exists
$existingUsers = supabaseSelect('users', ['email' => $email]);

if (!empty($existingUsers)) {
  header("Location: register.php?error=Email already registered");
  exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user WITH age and gender from registration
$result = supabaseInsert('users', [
  'fullname' => $fullname,
  'email' => $email,
  'password' => $hashed_password,
  'gender' => $gender,  // Store gender from registration
  'age' => $age,        // Store age from registration
  'role' => 'Patient'   // Default role
]);

if (isset($result['error'])) {
  header("Location: register.php?error=Registration failed");
} else {
  header("Location: register.php?success=1");
}
exit;