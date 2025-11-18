<?php
session_start();
include 'supabase.php';

$user_id = $_SESSION['user']['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $q1 = $_POST['q1'];
  $q2 = $_POST['q2'];
  $q3 = $_POST['q3'];
  $score = $q1 + $q2 + $q3;

  if ($user_id) {
    $stmt = $conn->prepare("INSERT INTO pre_assessments (user_id, q1, q2, q3, score, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiiii", $user_id, $q1, $q2, $q3, $score);
    $stmt->execute();
  }

  header("Location: pre_result.php?score=" . $score);
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MindCare | Pre-Assessment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">

  <style>
    /* Layout */
    .main-container {
      margin-left: 270px;
      margin-top: 60px;
      padding: 40px;
      width: calc(100% - 270px);
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Assessment card */
    .assessment-card {
      background-color: #ffffff;
      border-radius: 16px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
      padding: 50px 70px;
      max-width: 900px;
      margin: 0 auto;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    body.dark-mode .assessment-card {
      background-color: #1f1f1f;
      color: #f8f9fa;
    }

    .assessment-card h3 {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 25px;
    }

    select.form-select {
      padding: 10px;
      font-size: 1rem;
    }

    .btn-success {
      font-size: 1.05rem;
      padding: 10px 0;
      border-radius: 10px;
    }

    @media (max-width: 768px) {
      .main-container {
        margin-left: 0;
        padding: 20px;
        width: 100%;
      }

      .assessment-card {
        padding: 30px 25px;
        max-width: 95%;
      }
    }
  </style>
</head>
<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar d-flex flex-column align-items-center p-3">
      <div class="logo-wrapper mb-3">
        <img src="images/MindCare.png" alt="MindCare Logo" class="logo-img" style="max-width: 120px;">
      </div>

      <nav class="nav flex-column w-100 text-center">
        <a class="nav-link fw-bold" href="assessment.php">Assessment</a>
        <a class="nav-link" href="recommendations.php">Recommendations</a>
        <a class="nav-link" href="book_appointment.php">Book Appointment</a>
        <a class="nav-link" href="appointments.php">My Appointments</a>
        <a class="nav-link" href="profile.php">Profile</a>
        <a class="nav-link" href="faq.php">FAQ</a>
        <a class="nav-link text-danger fw-bold" href="logout.php">Logout</a>
      </nav>

      <button id="themeToggle" class="btn btn-outline-light d-flex align-items-center gap-2 mt-4">
        <span id="themeIcon">🌞</span>
        <span id="themeLabel">Light Mode</span>
      </button>
    </div>

    <!-- Main Content -->
    <div class="main-container fade-in">
      <div class="assessment-card">
        <h3>🧠 Quick Mental Health Check</h3>

        <?php if (!$user_id): ?>
          <div class="alert alert-info">
            You’re taking this assessment as a guest. 
            <a href="login.php" class="text-primary fw-bold">Log in</a> 
            to save your results and track your progress.
          </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
          <div class="mb-4">
            <label for="q1" class="form-label">1. How often have you felt down or hopeless in the past week?</label>
            <select name="q1" id="q1" class="form-select" required>
              <option value="" disabled selected>Select an option</option>
              <option value="0">Not at all</option>
              <option value="1">Several days</option>
              <option value="2">More than half the days</option>
              <option value="3">Nearly every day</option>
            </select>
          </div>

          <div class="mb-4">
            <label for="q2" class="form-label">2. How often have you felt anxious or on edge?</label>
            <select name="q2" id="q2" class="form-select" required>
              <option value="" disabled selected>Select an option</option>
              <option value="0">Not at all</option>
              <option value="1">Several days</option>
              <option value="2">More than half the days</option>
              <option value="3">Nearly every day</option>
            </select>
          </div>

          <div class="mb-4">
            <label for="q3" class="form-label">3. How often have you had trouble sleeping or concentrating?</label>
            <select name="q3" id="q3" class="form-select" required>
              <option value="" disabled selected>Select an option</option>
              <option value="0">Not at all</option>
              <option value="1">Several days</option>
              <option value="2">More than half the days</option>
              <option value="3">Nearly every day</option>
            </select>
          </div>

          <button type="submit" class="btn btn-success w-100">Submit Assessment</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Bootstrap validation
    (() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();

    // Dark mode toggle
    const toggleBtn = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const label = document.getElementById('themeLabel');

    const prefersDark = localStorage.getItem('dark-mode') === 'true';
    if (prefersDark) {
      document.body.classList.add('dark-mode');
      icon.textContent = '🌙';
      label.textContent = 'Dark Mode';
    }

    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      const isDark = document.body.classList.contains('dark-mode');
      localStorage.setItem('dark-mode', isDark);
      icon.textContent = isDark ? '🌙' : '🌞';
      label.textContent = isDark ? 'Dark Mode' : 'Light Mode';
    });
  </script>
</body>
</html>