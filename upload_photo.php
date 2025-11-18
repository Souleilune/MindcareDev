<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user']['id'];
if ($_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
  $target = 'uploads/profile_' . $user_id . '.jpg';
  move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target);
}
header("Location: dashboard.php");
?>