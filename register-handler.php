<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include 'supabase.php';

$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$gender = $_POST['gender']; 

// Check if email already exists
$existingUsers = supabaseSelect('users', ['email' => $email]);

if (!empty($existingUsers)) {
  header("Location: register.php?error=Email already registered");
  exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user with gender
$result = supabaseInsert('users', [
  'fullname' => $fullname,
  'email' => $email,
  'password' => $hashed_password,
  'gender' => $gender,
  'role' => 'Patient' // Default role
]);

if (isset($result['error'])) {
  header("Location: register.php?error=Registration failed");
} else {
  header("Location: register.php?success=1");
}
exit;