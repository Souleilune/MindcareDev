<?php
session_start();
include 'supabase.php';

// Restrict access to Admin or Specialist
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['Admin', 'Specialist'])) {
  echo "<script>alert('Access denied.'); window.location.href='login.php';</script>";
  exit;
}

$appointment_id = $_POST['appointment_id'];
$status = $_POST['status'];

$valid_statuses = ['Confirmed', 'Completed', 'Cancelled'];
if (!in_array($status, $valid_statuses)) {
  echo "<script>alert('Invalid status.'); window.history.back();</script>";
  exit;
}

$result = supabaseUpdate('appointments', ['id' => $appointment_id], [
  'status' => $status
]);

if (isset($result['error'])) {
  echo "<script>alert('Failed to update status.'); window.history.back();</script>";
} else {
  echo "<script>alert('Status updated successfully.'); window.location.href='admin_appointments.php';</script>";
}
exit;