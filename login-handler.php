<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include 'supabase.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
  header("Location: login.php?error=" . urlencode("Email and password are required"));
  exit;
}

// Debug: Log the email being searched
error_log("Login attempt for email: " . $email);

// Get user by email using Supabase REST API with RLS bypass
// We need to bypass RLS here because we're not authenticated yet
$users = supabaseSelect('users', ['email' => $email], '*', null, null, true);

// Debug: Log the response
error_log("Supabase response count: " . count($users));

if (empty($users)) {
  error_log("No user found for email: " . $email);
  header("Location: login.php?error=" . urlencode("Email not found"));
  exit;
}

$user = $users[0];

// Verify password
if (!password_verify($password, $user['password'])) {
  error_log("Incorrect password for email: " . $email);
  header("Location: login.php?error=" . urlencode("Incorrect password"));
  exit;
}

// Set session data
$_SESSION['user'] = [
  'id' => $user['id'],
  'fullname' => $user['fullname'],
  'email' => $user['email'],
  'gender' => $user['gender'] ?? null,
  'role' => $user['role'] ?? 'Patient',
  'created_at' => $user['created_at']
];

error_log("Login successful for user ID: " . $user['id']);

// Redirect based on role
if ($user['role'] === 'Admin') {
  header("Location: admin_appointments.php");
} elseif ($user['role'] === 'Specialist') {
  header("Location: specialist_dashboard.php");
} else {
  header("Location: dashboard.php");
}
exit;