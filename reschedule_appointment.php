<?php
session_start();
include 'supabase.php';

$appointment_id = $_GET['appointment_id'] ?? null;

if ($appointment_id) {
  $_SESSION['reschedule_id'] = $appointment_id;
  header("Location: book_appointment.php?reschedule=1");
  exit;
}