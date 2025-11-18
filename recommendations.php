<?php
session_start();
include 'supabase.php';

$user_id = $_SESSION['user']['id'];

// Get the latest assessment
$assessments = supabaseSelect(
  'assessments',
  ['user_id' => $user_id],
  '*',
  'created_at.desc',
  1
);

$assessment = !empty($assessments) ? $assessments[0] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Recommendations</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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

      <div class="dropdown w-100 mb-3">
        <button class="btn btn-outline-light dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          Resources
        </button>
        <ul class="dropdown-menu w-100">
          <li><h6 class="dropdown-header">Services</h6></li>
          <li><span class="dropdown-item-text">Mental Health Assessments</span></li>
          <li><span class="dropdown-item-text">Personalized Recommendations</span></li>
          <li><span class="dropdown-item-text">Appointment Booking</span></li>
          <li><span class="dropdown-item-text">Progress Tracking Dashboard</span></li>
          <li><hr class="dropdown-divider"></li>
          <li><h6 class="dropdown-header">Therapies</h6></li>
          <li><span class="dropdown-item-text">Cognitive Behavioral Therapy</span></li>
          <li><span class="dropdown-item-text">Mindfulness-Based Therapy</span></li>
          <li><span class="dropdown-item-text">Interpersonal Therapy</span></li>
          <li><span class="dropdown-item-text">Supportive Counseling</span></li>
        </ul>
      </div>

      <nav class="nav flex-column w-100 text-center">
        <a class="nav-link" href="assessment.php">Assessment</a>
        <a class="nav-link fw-bold" href="recommendations.php">Recommendations</a>
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
      <div class="recommendation-section mx-auto" style="max-width: 600px;">
        <h3 class="mb-3">Your Recommendations</h3>
        <?php if ($assessment): ?>
          <p class="text-muted">Based on your latest assessment: <strong><?= htmlspecialchars($assessment['summary']) ?></strong></p>
        <?php else: ?>
          <div class="alert alert-info">
            You haven't taken an assessment yet. <a href="assessment.php">Take one now</a> to get personalized recommendations.
          </div>
        <?php endif; ?>

        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title text-primary">Recommended Actions</h5>
            <ul class="recommendation-list">
              <li>🧘 Try guided breathing exercises</li>
              <li>📓 Start a daily journal</li>
              <li>📞 Talk to a specialist if symptoms persist</li>
              <li>🎧 Listen to calming music or nature sounds</li>
              <li>🚶 Take short mindful walks outdoors</li>
              <li>📚 Read something uplifting or inspiring</li>
              <li>🛌 Maintain a consistent sleep schedule</li>
              <li>🍵 Limit caffeine and sugar intake</li>
              <li>🤝 Connect with a trusted friend or support group</li>
              <li>🧠 Practice positive self-talk and affirmations</li>
            </ul>
            <a href="book_appointment.php" class="btn btn-success mt-3">💬 Book a Consultation</a>
          </div>
        </div>
      </div>
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