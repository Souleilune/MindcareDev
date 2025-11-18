<?php
session_start();
include 'supabase.php';

$user_id = $_POST['user_id'];
$q1 = (int)$_POST['q1'];
$q2 = (int)$_POST['q2'];
$score = $q1 + $q2;

// Interpret score
if ($score <= 2) {
  $summary = "Mild symptoms";
} elseif ($score <= 4) {
  $summary = "Moderate symptoms";
} else {
  $summary = "Severe symptoms";
}

// Save to database
$result = supabaseInsert('assessments', [
  'user_id' => $user_id,
  'score' => $score,
  'summary' => $summary
]);

// Add notification
if (!isset($result['error'])) {
  $message = "Your new mental health assessment has been submitted.";
  supabaseInsert('notifications', [
    'user_id' => $user_id,
    'message' => $message
  ]);
}

header("Location: recommendations.php");
exit;