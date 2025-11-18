<?php
include 'supabase.php';

$fullname = "Test User";
$email = "test@example.com";
$password = password_hash("123456", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $fullname, $email, $password);
$stmt->execute();

echo "User added successfully!";
?>