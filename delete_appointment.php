<?php
session_start();
include 'supabase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $appointment_id = $_POST['appointment_id'] ?? null;
  $user_id = $_SESSION['user']['id'] ?? null;

  if ($appointment_id && $user_id) {
    supabaseDelete('appointments', [
      'id' => $appointment_id,
      'user_id' => $user_id
    ]);
  }
}

header("Location: appointments.php");
exit;