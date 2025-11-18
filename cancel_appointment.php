<?php
session_start();
include 'supabase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $appointment_id = $_POST['appointment_id'] ?? null;
  $user_id = $_SESSION['user']['id'] ?? null;

  if ($appointment_id && $user_id) {
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $appointment_id, $user_id);
    $stmt->execute();
  }
}

header("Location: index.php");
exit;