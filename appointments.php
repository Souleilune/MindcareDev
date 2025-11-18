<?php
session_start();
include 'supabase.php';

$user_id = $_SESSION['user']['id'];
$user_name = $_SESSION['user']['fullname'] ?? 'User';

// Get appointments with specialist information using Supabase foreign key syntax
$appointments = supabaseSelect(
  'appointments',
  ['user_id' => $user_id],
  'id,appointment_date,appointment_time,status,users:specialist_id(fullname)',
  'appointment_date.asc'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Appointments - MindCare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --primary-teal: #5ad0be;
      --primary-teal-dark: #1aa592;
      --text-dark: #2b2f38;
      --text-muted: #7a828e;
      --bg-light: #f8f9fa;
      --sidebar-bg: #f5f6f7;
      --card-bg: #ffffff;
      --border-color: #e9edf5;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--bg-light);
      color: var(--text-dark);
      overflow-x: hidden;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Dark Mode Variables */
    body.dark-mode {
      --bg-light: #1a1a1a;
      --sidebar-bg: #2a2a2a;
      --card-bg: #2a2a2a;
      --text-dark: #f1f1f1;
      --text-muted: #b0b0b0;
      --border-color: #3a3a3a;
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      width: 250px;
      height: 100vh;
      background: var(--sidebar-bg);
      border-right: 1px solid var(--border-color);
      padding: 1.5rem;
      z-index: 1000;
      display: flex;
      flex-direction: column;
      transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    .sidebar .logo-wrapper {
      text-align: center;
      margin-bottom: 2rem;
    }

    .sidebar .logo-img {
      max-width: 125px;
    }

    .sidebar .nav-link {
      color: var(--text-dark);
      padding: 0.65rem 1rem;
      border-radius: 8px;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 500;
      font-size: 0.625rem;
      text-decoration: none;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover {
      background-color: rgba(90, 208, 190, 0.1);
      color: #5ad0be;
    }

    .sidebar .nav-link.active {
      background-color: #5ad0be;
      color: #ffffff;
    }

    /* Dark Mode Toggle Button */
    .theme-toggle {
      margin-top: auto;
      padding-top: 1rem;
      border-top: 1px solid var(--border-color);
    }

    .theme-toggle button {
      width: 100%;
      padding: 0.65rem 1rem;
      background: transparent;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      color: var(--text-dark);
      font-size: 0.625rem;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
    }

    .theme-toggle button:hover {
      background-color: rgba(90, 208, 190, 0.1);
      border-color: var(--primary-teal);
      color: var(--primary-teal);
    }

    /* Main Content Area */
    .main-wrapper {
      margin-left: 250px;
      padding: 2rem;
      width: calc(100% - 250px);
      min-height: 100vh;
    }

    .content-inner {
      max-width: 100%;
      width: 100%;
    }

    /* Header */
    .page-header {
      margin-bottom: 2rem;
    }

    .page-header h1 {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
    }

    .page-header h1 .user-name {
      color: var(--primary-teal);
    }

    .page-header .subtitle {
      color: var(--text-muted);
      font-size: 0.95rem;
    }

    /* Alert */
    .alert {
      border-radius: 8px;
      border: none;
      margin-bottom: 2rem;
    }

    .alert-success {
      background-color: rgba(90, 208, 190, 0.1);
      color: var(--primary-teal-dark);
    }

    body.dark-mode .alert-success {
      background-color: rgba(90, 208, 190, 0.2);
      color: var(--primary-teal);
    }

    .alert-info {
      background-color: rgba(90, 208, 190, 0.05);
      color: var(--text-muted);
      border: 1px solid var(--border-color);
    }

    /* Section Box */
    .section-box {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    body.dark-mode .section-box {
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .section-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--text-dark);
      margin: 0 0 1.5rem 0;
    }

    /* Table Styling */
    .appointments-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin-top: 1rem;
    }

    .appointments-table thead tr {
      background: var(--bg-light);
    }

    .appointments-table th {
      padding: 1rem;
      text-align: left;
      font-weight: 600;
      font-size: 0.85rem;
      color: var(--text-dark);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid var(--border-color);
    }

    .appointments-table td {
      padding: 1rem;
      border-bottom: 1px solid var(--border-color);
      font-size: 0.9rem;
      color: var(--text-dark);
    }

    .appointments-table tbody tr {
      transition: background-color 0.2s ease;
    }

    .appointments-table tbody tr:hover {
      background-color: rgba(90, 208, 190, 0.03);
    }

    body.dark-mode .appointments-table tbody tr:hover {
      background-color: rgba(90, 208, 190, 0.08);
    }

    /* Status Badge */
    .status-badge {
      display: inline-block;
      padding: 0.35rem 0.75rem;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 500;
      text-transform: capitalize;
    }

    .status-badge.pending {
      background-color: rgba(255, 193, 7, 0.1);
      color: #f57c00;
    }

    .status-badge.confirmed {
      background-color: rgba(76, 175, 80, 0.1);
      color: #2e7d32;
    }

    body.dark-mode .status-badge.confirmed {
      background-color: rgba(76, 175, 80, 0.2);
      color: #81c784;
    }

    .status-badge.cancelled {
      background-color: rgba(244, 67, 54, 0.1);
      color: #c62828;
    }

    body.dark-mode .status-badge.cancelled {
      background-color: rgba(244, 67, 54, 0.2);
      color: #ef5350;
    }

    /* Action Buttons */
    .action-buttons {
      display: flex;
      gap: 0.5rem;
    }

    .btn-action {
      padding: 0.4rem 0.9rem;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 500;
      border: 1px solid;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-cancel {
      background: transparent;
      border-color: #ef5350;
      color: #ef5350;
    }

    .btn-cancel:hover {
      background: #ef5350;
      color: white;
    }

    .btn-reschedule {
      background: transparent;
      border-color: #ffa726;
      color: #ffa726;
    }

    .btn-reschedule:hover {
      background: #ffa726;
      color: white;
    }

    /* Back Button */
    .btn-back {
      background: transparent;
      border: 1px solid var(--border-color);
      color: var(--text-muted);
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
      font-size: 0.9rem;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      margin-top: 1rem;
    }

    .btn-back:hover {
      background: var(--bg-light);
      border-color: var(--primary-teal);
      color: var(--primary-teal);
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
    }

    .empty-state svg {
      width: 80px;
      height: 80px;
      margin-bottom: 1rem;
      opacity: 0.3;
      stroke: var(--text-muted);
    }

    .empty-state h4 {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
    }

    .empty-state p {
      color: var(--text-muted);
      font-size: 0.9rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .main-wrapper {
        margin-left: 0;
        width: 100%;
        padding: 1.5rem;
      }

      .section-box {
        padding: 1.5rem;
        overflow-x: auto;
      }

      .appointments-table {
        min-width: 600px;
      }

      .action-buttons {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo-wrapper">
      <img src="images/Mindcare.png" alt="MindCare Logo" class="logo-img" />
    </div>

    <nav class="nav flex-column" style="flex: 1;">
      <a class="nav-link" href="dashboard.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        DASHBOARD
      </a>
      <a class="nav-link" href="assessment.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        ASSESSMENT
      </a>
      <a class="nav-link" href="book_appointment.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        BOOK APPOINTMENT
      </a>
      <a class="nav-link active" href="appointments.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
        MY APPOINTMENTS
      </a>
      <a class="nav-link" href="profile.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        PROFILE
      </a>
      <a class="nav-link" href="faq.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        FAQS
      </a>
    </nav>

    <!-- Dark Mode Toggle -->
    <div class="theme-toggle">
      <button id="themeToggle">
        <span id="themeIcon">🌞</span>
        <span id="themeLabel">Light Mode</span>
      </button>
    </div>

    <!-- Logout Button -->
    <a href="logout.php" class="nav-link" style="margin-top: 1rem; color: #ef5350; border-top: 1px solid var(--border-color); padding-top: 1rem;">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
      LOGOUT
    </a>
  </div>

  <!-- Main Content -->
  <div class="main-wrapper">
    <div class="content-inner">
      
      <!-- Header -->
      <div class="page-header">
        <h1>Hello, <span class="user-name"><?= htmlspecialchars($user_name) ?></span>!</h1>
        <p class="subtitle">Manage your appointments with our specialists</p>
      </div>

      <!-- Success Alert -->
      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_GET['success']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Appointments Section -->
      <div class="section-box">
        <h5 class="section-title">Your Appointments</h5>

        <?php if (count($appointments) > 0): ?>
          <table class="appointments-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Specialist</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($appointments as $row): ?>
                <tr>
                  <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                  <td><?= date('g:i A', strtotime($row['appointment_time'])) ?></td>
                  <td><?= htmlspecialchars($row['users']['fullname'] ?? 'N/A') ?></td>
                  <td>
                    <span class="status-badge <?= strtolower($row['status']) ?>">
                      <?= ucfirst($row['status']) ?>
                    </span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <form method="POST" action="delete_appointment.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                        <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn-action btn-cancel">Cancel</button>
                      </form>
                      <form method="GET" action="reschedule_appointment.php" style="display: inline;">
                        <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn-action btn-reschedule">Reschedule</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <h4>No Appointments Yet</h4>
            <p>You haven't booked any appointments. Book your first appointment to get started!</p>
          </div>
        <?php endif; ?>

        <a href="book_appointment.php" class="btn-back">📅 Book New Appointment</a>
      </div>

    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Dark mode toggle
    const toggleBtn = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const label = document.getElementById('themeLabel');

    // Check for saved theme preference
    const prefersDark = localStorage.getItem('dark-mode') === 'true';
    if (prefersDark) {
      document.body.classList.add('dark-mode');
      icon.textContent = '🌙';
      label.textContent = 'Dark Mode';
    }

    // Toggle theme
    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      const isDark = document.body.classList.contains('dark-mode');
      localStorage.setItem('dark-mode', isDark);
      
      // Animate icon
      icon.style.transform = 'rotate(360deg)';
      setTimeout(() => icon.style.transform = 'rotate(0deg)', 500);
      
      // Update icon and label
      icon.textContent = isDark ? '🌙' : '🌞';
      label.textContent = isDark ? 'Dark Mode' : 'Light Mode';
    });

    // Smooth transition for icon
    icon.style.transition = 'transform 0.5s ease';
  </script>
</body>
</html>