<?php
session_start();
include 'supabase.php';

// Ensure user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Patient') {
  echo "<script>alert('Access denied. Please login.'); window.location.href='login.php';</script>";
  exit;
}

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: book_appointment.php");
  exit;
}

$user_id = $_SESSION['user']['id'];
$specialist_id = $_POST['specialist_id'] ?? null;
$appointment_date = $_POST['appointment_date'] ?? null;
$appointment_time = $_POST['appointment_time'] ?? null;

// Validate required fields
if (!$specialist_id || !$appointment_date || !$appointment_time) {
  echo "<script>alert('Please select a specialist, date, and time.'); window.location.href='book_appointment.php';</script>";
  exit;
}

// Convert time format from "HH:MM AM/PM" to "HH:MM:SS" (24-hour format)
$timeFormatted = date("H:i:s", strtotime($appointment_time));

// Check if the specialist exists and is actually a specialist
$specialist = supabaseSelect(
  'users',
  ['id' => (int)$specialist_id, 'role' => 'Specialist'],
  'id,fullname'
);

if (empty($specialist)) {
  echo "<script>alert('Invalid specialist selected.'); window.location.href='book_appointment.php';</script>";
  exit;
}

// Check if the time slot is already booked
$existingAppointments = supabaseSelect(
  'appointments',
  [
    'specialist_id' => (int)$specialist_id,
    'appointment_date' => $appointment_date,
    'appointment_time' => $timeFormatted
  ],
  'id'
);

if (!empty($existingAppointments)) {
  echo "<script>alert('This time slot is already booked. Please choose another time.'); window.location.href='book_appointment.php';</script>";
  exit;
}

// Insert the appointment into Supabase
$result = supabaseInsert('appointments', [
  'user_id' => (int)$user_id,
  'specialist_id' => (int)$specialist_id,
  'appointment_date' => $appointment_date,
  'appointment_time' => $timeFormatted,
  'status' => 'Pending',
  'notes' => null
]);

// Check for errors
if (isset($result['error'])) {
  error_log("Appointment booking error: " . json_encode($result['error']));
  echo "<script>alert('Failed to book appointment. Please try again.'); window.location.href='book_appointment.php';</script>";
  exit;
}

// Success - redirect to appointments page
$specialist_name = $specialist[0]['fullname'];
$formatted_date = date('F j, Y', strtotime($appointment_date));
$formatted_time = date('g:i A', strtotime($timeFormatted));

$success_message = "Appointment successfully booked with {$specialist_name} on {$formatted_date} at {$formatted_time}!";
header("Location: appointments.php?success=" . urlencode($success_message));
exit;