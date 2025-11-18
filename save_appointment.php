<?php
include 'supabase.php';

$user_id = $_POST['user_id'];
$specialist_id = $_POST['specialist'];
$date = $_POST['date'];
$time = $_POST['time'];

// Check if time slot is already booked
$existingAppointments = supabaseSelect('appointments', [
  'specialist_id' => $specialist_id,
  'appointment_date' => $date,
  'appointment_time' => $time
]);

if (!empty($existingAppointments)) {
  echo "<script>alert('This time slot is already booked. Please choose another.'); window.history.back();</script>";
  exit;
}

// Insert the appointment
$result = supabaseInsert('appointments', [
  'user_id' => (int)$user_id,
  'specialist_id' => (int)$specialist_id,
  'appointment_date' => $date,
  'appointment_time' => $time,
  'status' => 'Confirmed'
]);

if (isset($result['error'])) {
  echo "<script>alert('Failed to book appointment. Please try again.'); window.history.back();</script>";
  exit;
}

// Redirect after saving
header("Location: appointments.php");
exit;