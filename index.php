<?php
session_start();
include 'supabase.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user']['id'];
$user_name = $_SESSION['user']['fullname'];

// Get latest assessment
$assessments = supabaseSelect(
  'assessments',
  ['user_id' => $user_id],
  'summary',
  'created_at.desc',
  1
);
$assessment = !empty($assessments) ? $assessments[0] : null;

// Get upcoming appointment
$appointments = supabaseSelect(
  'appointments',
  [
    'user_id' => $user_id,
    'status' => ['operator' => 'in', 'value' => '("Confirmed","Pending")'],
    'appointment_date' => ['operator' => 'gte', 'value' => date('Y-m-d')]
  ],
  'id,appointment_date,appointment_time,specialist_id,status,users:specialist_id(fullname)',
  'appointment_date.asc',
  1
);
$appointment = !empty($appointments) ? $appointments[0] : null;

// Get unread notifications
$notifications = supabaseSelect(
  'notifications',
  [
    'user_id' => $user_id,
    'is_read' => false
  ],
  '*',
  'created_at.desc'
);

$specialist_name = '';
if ($appointment && isset($appointment['users'])) {
  $specialist_name = $appointment['users']['fullname'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mental Health App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
  <style>
    #themeToggle {
      transition: background-color 0.3s, color 0.3s, transform 0.2s;
      font-weight: 600;
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
    }

    #themeToggle:active {
      transform: scale(0.95);
    }

    #themeIcon {
      display: inline-block;
      transition: transform 0.5s ease;
    }

    #themeIcon.rotate {
      transform: rotate(360deg);
    }

    #themeLabel {
      transition: opacity 0.3s ease;
      opacity: 1;
    }

   .sidebar {
      background: linear-gradient(to bottom right, #d0f0c0, #a8e6cf);
      width: 250px;
      min-height: 100vh;
      border-right: 1px solid #ddd;
      transition: background-color 0.3s, color 0.3s;
    }

    body.dark-mode .sidebar {
      background: linear-gradient(to bottom right, #2e7d32, #1b5e20);
      color: #f1f1f1;
      border-color: #444;
    }

    .sidebar h4 {
      font-weight: bold;
      color: #0d6efd;
    }

    body.dark-mode .sidebar h4 {
      color: #90caf9;
    }

    .sidebar .nav-link {
      padding: 10px 12px;
      border-radius: 6px;
      transition: background-color 0.3s, color 0.3s;
    }

    .sidebar .nav-link:hover {
      background-color: #0d6efd;
      color: #fff !important;
    }

    body.dark-mode .sidebar .nav-link:hover {
      background-color: #1f6feb;
    }
  </style>
</head>
<body>
  <!-- Welcome Screen -->
  <div id="welcome-screen" class="d-flex justify-content-center align-items-center vh-100 bg-primary text-white">
    <h1 class="fade-in">Welcome to MindCare 🌿</h1>
  </div>

<!-- Main Layout -->
<div class="d-flex">
<!-- Sidebar -->
<div id="sidebar" class="sidebar p-3 d-flex flex-column align-items-center">
  <!-- Centered Logo -->
  <div class="logo-wrapper mb-2">
    <img src="images/MindCare.png" alt="MindCare Logo" class="logo-img" />
  </div>

  <!-- Resources Dropdown -->
  <div class="dropdown w-100 mb-3">
    <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      🧠 Resources
    </button>
    <ul class="dropdown-menu w-100">
      <li><h6 class="dropdown-header">💼 Services</h6></li>
      <li><span class="dropdown-item-text">Mental Health Assessments</span></li>
      <li><span class="dropdown-item-text">Personalized Recommendations</span></li>
      <li><span class="dropdown-item-text">Appointment Booking</span></li>
      <li><span class="dropdown-item-text">Progress Tracking Dashboard</span></li>
      <li><hr class="dropdown-divider"></li>
      <li><h6 class="dropdown-header">🧑‍⚕️ Therapies</h6></li>
      <li><span class="dropdown-item-text">Cognitive Behavioral Therapy</span></li>
      <li><span class="dropdown-item-text">Mindfulness-Based Therapy</span></li>
      <li><span class="dropdown-item-text">Interpersonal Therapy</span></li>
      <li><span class="dropdown-item-text">Supportive Counseling</span></li>
    </ul>
  </div>

  <!-- Navigation Links -->
  <nav class="nav flex-column w-100 text-center">
    <a class="nav-link" href="assessment.php">Assessment</a>
    <a class="nav-link" href="recommendations.php">Recommendations</a>
    <a class="nav-link" href="book_appointment.php">Book Appointment</a>
    <a class="nav-link" href="appointments.php">My Appointments</a>
    <a class="nav-link" href="profile.php">Profile</a>
    <a class="nav-link" href="faq.php">FAQ</a>
    <a class="nav-link text-danger" href="logout.php">Logout</a>
  </nav>

  <!-- 🌗 Sun/Moon Toggle -->
  <button id="themeToggle" class="btn btn-outline-secondary d-flex align-items-center gap-2 mt-4">
    <span id="themeIcon">🌞</span>
    <span id="themeLabel">Light Mode</span>
  </button>
</div>

    <!-- Main App -->
    <div id="main-app" class="container-fluid mt-4 fade-in d-none">
      <!-- App Header -->
      <header class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold"> Dashboard</h2>
          <p class="text-muted mb-0 dashboard-subtitle">Your mental wellness at a glance!</p>
        </div>
        <div class="position-relative">
          <button class="btn btn-light position-relative" id="notificationBell" data-bs-toggle="dropdown" aria-expanded="false">
            🔔
            <?php if (count($notifications) > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= count($notifications) ?>
              </span>
            <?php endif; ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" id="notificationDropdown">
            <?php if (count($notifications) === 0): ?>
              <li class="dropdown-item text-muted">No new notifications</li>
            <?php else: ?>
              <?php foreach ($notifications as $note): ?>
                <li class="dropdown-item">
                  <?= htmlspecialchars($note['message']) ?><br>
                  <small class="text-muted"><?= date('M d, H:i', strtotime($note['created_at'])) ?></small>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        </div>
      </header>

      <!-- Welcome Section -->
      <div class="card card-welcome mb-3">
        <div class="card-body">
          <div class="alert alert-info mb-0">
            👋 Welcome back, <strong><?= htmlspecialchars($user_name) ?></strong>!
          </div>
        </div>
      </div>

      <!-- Quick Assessment Summary -->
      <div class="card mb-3 card-assessment">
        <div class="card-body">
          <h5 class="card-title">Quick Assessment Summary</h5>
          <p class="card-text">
            <?= $assessment ? "Your last score: <strong>{$assessment['summary']}</strong>" : "No assessment taken yet." ?>
          </p>
          <a href="assessment.php" class="btn btn-sm btn-outline-primary">Take Assessment</a>
          <a href="recommendations.php" class="btn btn-sm btn-outline-success">View Recommendations</a>
        </div>
      </div>

      <!-- Upcoming Appointment -->
      <div class="card mb-3 card-appointment">
        <div class="card-body">
          <h5 class="card-title">Upcoming Appointment</h5>
          <?php if ($appointment): ?>
            <p class="card-text">
              <?= date('M d, Y', strtotime($appointment['appointment_date'])) ?> at <?= date('g:i A', strtotime($appointment['appointment_time'])) ?> –
              <?= htmlspecialchars($specialist_name) ?> (Specialist)<br>
              <span class="badge bg-secondary"><?= $appointment['status'] ?></span>
            </p>
            <div class="d-flex gap-2">
              <form method="POST" action="cancel_appointment.php">
                <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
              </form>
              <form method="GET" action="reschedule_appointment.php">
                <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-warning">Reschedule</button>
              </form>
            </div>
          <?php else: ?>
            <p class="card-text">No upcoming appointments.</p>
          <?php endif; ?>
          <a href="book_appointment.php" class="btn btn-sm btn-outline-info mt-2">Book Appointment</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
  window.onload = () => {
    setTimeout(() => {
      document.getElementById("welcome-screen").classList.add("d-none");
      document.getElementById("main-app").classList.remove("d-none");
    }, 2500);

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
  }
</script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>