<?php
session_start();
include 'supabase.php';

$score = isset($_GET['score']) ? (int)$_GET['score'] : null;

function getFeedback($score) {
  if ($score === null) return "No score received.";
  if ($score <= 2) return "Your responses suggest minimal distress. Keep practicing self-care and stay mindful.";
  if ($score <= 5) return "You may be experiencing mild symptoms. Consider journaling or relaxation techniques.";
  if ($score <= 7) return "Moderate signs of emotional strain. Talking to a specialist could be helpful.";
  return "Your score indicates significant distress. We strongly recommend creating an account here at MindCare and book a consultation with our professionals.";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Pre-Assessment Result</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar d-flex flex-column align-items-center p-3">
      <div class="logo-wrapper mb-3">
        <img src="images/MindCare.png" alt="MindCare Logo" class="logo-img" style="max-width: 120px;" />
      </div>

      <nav class="nav flex-column w-100 text-center">
        <a class="nav-link" href="assessment.php">Assessment</a>
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
    <div class="container py-5 fade-in">
      <h3 class="mb-4">🧠 Your Pre-Assessment Result</h3>

      <?php if ($score !== null): ?>
        <div class="card mb-4 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Your Score: <span class="text-primary"><?= $score ?></span></h5>
            <p class="card-text"><?= getFeedback($score) ?></p>

            <div class="d-flex flex-wrap gap-2 mt-3">
              <a href="book_appointment.php" class="btn btn-success">💬 Book a Consultation</a>

              <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-primary">🧾 Create an Account</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">No score was provided. Please retake the assessment.</div>
        <a href="assessment.php" class="btn btn-outline-primary">← Back to Assessment</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
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
      icon.classList.add('rotate');
      setTimeout(() => icon.classList.remove('rotate'), 500);
      icon.textContent = isDark ? '🌙' : '🌞';
      label.textContent = isDark ? 'Dark Mode' : 'Light Mode';
    });
  </script>
</body>
</html>