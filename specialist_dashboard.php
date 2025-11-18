<?php
session_start();
include 'supabase.php';

// Restrict access to specialists only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Specialist') {
  echo "<script>alert('Access denied.'); window.location.href='login.php';</script>";
  exit;
}

$specialist_id = $_SESSION['user']['id'];
$specialist_name = $_SESSION['user']['fullname'];

// Fetch recent bookings (last 7 days) using Supabase
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
$recentAppointments = supabaseSelect(
  'appointments',
  [
    'specialist_id' => $specialist_id,
    'created_at' => ['operator' => 'gte', 'value' => $sevenDaysAgo]
  ],
  'id,user_id,appointment_date,appointment_time,status,created_at,users:user_id(fullname)',
  'created_at.desc'
);

// Limit to 10 results
$recent_bookings = array_slice($recentAppointments, 0, 10);

// Fetch all appointments for booking management
$allAppointments = supabaseSelect(
  'appointments',
  ['specialist_id' => $specialist_id],
  'id,user_id,appointment_date,appointment_time,status,notes,created_at,users:user_id(fullname,email,gender)',
  'appointment_date.desc,appointment_time.desc'
);

// Get statistics
$total_appointments = count($allAppointments);
$confirmed = 0;
$pending = 0;
$completed = 0;
$cancelled = 0;

foreach ($allAppointments as $apt) {
  switch ($apt['status']) {
    case 'Confirmed':
      $confirmed++;
      break;
    case 'Pending':
      $pending++;
      break;
    case 'Completed':
      $completed++;
      break;
    case 'Cancelled':
      $cancelled++;
      break;
  }
}

$stats = [
  'total_appointments' => $total_appointments,
  'confirmed' => $confirmed,
  'pending' => $pending,
  'completed' => $completed,
  'cancelled' => $cancelled
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Specialist Dashboard - MindCare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
  <style>
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
      background-color: var(--bg-light);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: var(--text-dark);
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    body.dark-mode {
      --bg-light: #1a1a1a;
      --sidebar-bg: #2a2a2a;
      --card-bg: #2d2d2d;
      --text-dark: #f1f1f1;
      --text-muted: #b0b0b0;
      --border-color: #3a3a3a;
    }

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

    .main-content {
      margin-left: 250px;
      padding: 2rem;
      min-height: 100vh;
    }

    .dashboard-header {
      margin-bottom: 2rem;
    }

    .dashboard-header h1 {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
      transition: color 0.3s ease;
    }

    .dashboard-header h1 .user-name {
      color: var(--primary-teal);
    }

    .dashboard-header .date-time {
      color: var(--text-muted);
      font-size: 0.95rem;
      transition: color 0.3s ease;
    }

    .card {
      background-color: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }

    body.dark-mode .card {
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .card:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    body.dark-mode .card:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    }

    .card-title {
      font-size: 0.85rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.5rem;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .card-value {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 0;
      transition: color 0.3s ease;
    }

    .tab-navigation {
      display: inline-flex;
      background-color: var(--sidebar-bg);
      border-radius: 10px;
      padding: 4px;
      gap: 0;
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
      margin-bottom: 1.5rem;
    }

    .tab-btn {
      padding: 0.5rem 1.5rem;
      border: none;
      background-color: transparent;
      color: var(--primary-teal);
      font-weight: 500;
      font-size: 0.875rem;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .tab-btn.active {
      background-color: var(--primary-teal);
      color: #ffffff;
      box-shadow: 0 1px 3px rgba(90, 208, 190, 0.3);
    }

    .tab-btn:hover:not(.active) {
      color: var(--primary-teal-dark);
    }

    .table {
      background-color: var(--card-bg);
      border-color: var(--border-color);
      color: var(--text-dark);
    }

    .table thead {
      background-color: var(--sidebar-bg);
      color: var(--text-dark);
    }

    .table-hover tbody tr:hover {
      background-color: rgba(90, 208, 190, 0.05);
    }

    .badge {
      padding: 0.35rem 0.75rem;
      font-size: 0.75rem;
      font-weight: 600;
      border-radius: 6px;
    }

    .status-confirmed {
      background-color: #d4edda;
      color: #155724;
    }

    .status-pending {
      background-color: #fff3cd;
      color: #856404;
    }

    .status-completed {
      background-color: #d1ecf1;
      color: #0c5460;
    }

    .status-cancelled {
      background-color: #f8d7da;
      color: #721c24;
    }

    body.dark-mode .status-confirmed {
      background-color: rgba(34, 139, 34, 0.2);
      color: #90ee90;
    }

    body.dark-mode .status-pending {
      background-color: rgba(255, 215, 0, 0.2);
      color: #ffd700;
    }

    body.dark-mode .status-completed {
      background-color: rgba(23, 162, 184, 0.2);
      color: #5bc0de;
    }

    body.dark-mode .status-cancelled {
      background-color: rgba(220, 53, 69, 0.2);
      color: #ff6b6b;
    }

    .btn-primary {
      background-color: var(--primary-teal);
      border-color: var(--primary-teal);
    }

    .btn-primary:hover {
      background-color: var(--primary-teal-dark);
      border-color: var(--primary-teal-dark);
    }

    .alert {
      background-color: transparent;
      border: none;
      padding: 1rem 0;
    }

    h1, h2, h3, h4, h5, h6, p {
      color: var(--text-dark);
      transition: color 0.3s ease;
    }

    .text-muted {
      color: var(--text-muted) !important;
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.show {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
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
      <a class="nav-link active" href="specialist_dashboard.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        DASHBOARD
      </a>
      <a class="nav-link" href="logout.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        LOGOUT
      </a>
    </nav>

    <div class="theme-toggle">
      <button id="themeToggle">
        <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
        <span id="themeLabel">Light Mode</span>
      </button>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="dashboard-header">
      <h1>Welcome, <span class="user-name"><?= htmlspecialchars($specialist_name) ?></span></h1>
      <p class="date-time"><?= date('l, F j, Y') ?></p>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
      <div class="col-md-3 mb-3">
        <div class="card">
          <div class="card-title">Total Appointments</div>
          <div class="card-value"><?= $stats['total_appointments'] ?></div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card">
          <div class="card-title">Confirmed</div>
          <div class="card-value" style="color: #28a745;"><?= $stats['confirmed'] ?></div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card">
          <div class="card-title">Pending</div>
          <div class="card-value" style="color: #ffc107;"><?= $stats['pending'] ?></div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card">
          <div class="card-title">Completed</div>
          <div class="card-value" style="color: #17a2b8;"><?= $stats['completed'] ?></div>
        </div>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
      <button class="tab-btn active" id="dashboardTab">Dashboard</button>
      <button class="tab-btn" id="bookingsTab">Booking Management</button>
    </div>

    <!-- Dashboard Tab Content -->
    <div id="dashboardContent">
      <div class="card">
        <h5 style="margin-bottom: 1.5rem;">Recent Bookings (Last 7 Days)</h5>
        <?php if (!empty($recent_bookings)): ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Patient</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Status</th>
                  <th>Booked At</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_bookings as $row): ?>
                  <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['users']['fullname'] ?? 'N/A') ?></td>
                    <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                    <td><?= date('g:i A', strtotime($row['appointment_time'])) ?></td>
                    <td>
                      <span class="badge status-<?= strtolower($row['status']) ?>">
                        <?= $row['status'] ?>
                      </span>
                    </td>
                    <td><?= date('M d, Y g:i A', strtotime($row['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info">No recent bookings in the last 7 days.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Booking Management Tab Content -->
    <div id="bookingsContent" style="display: none;">
      <div class="card">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 style="margin-bottom: 0;">All Appointments</h5>
          <button class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
            Refresh
          </button>
        </div>

        <?php if (!empty($allAppointments)): ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Patient</th>
                  <th>Email</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Status</th>
                  <th>Notes</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($allAppointments as $row): ?>
                  <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                      <?= htmlspecialchars($row['users']['fullname'] ?? 'N/A') ?>
                      <?php if (isset($row['users']['gender']) && $row['users']['gender']): ?>
                        <br><small class="text-muted"><?= htmlspecialchars($row['users']['gender']) ?></small>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['users']['email'] ?? 'N/A') ?></td>
                    <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                    <td><?= date('g:i A', strtotime($row['appointment_time'])) ?></td>
                    <td>
                      <span class="badge status-<?= strtolower($row['status']) ?>">
                        <?= $row['status'] ?>
                      </span>
                    </td>
                    <td><?= $row['notes'] ? htmlspecialchars($row['notes']) : '<em class="text-muted">No notes</em>' ?></td>
                    <td>
                      <form method="POST" action="update_status.php" class="d-inline">
                        <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                        <select name="status" class="form-select form-select-sm" style="width: auto; display: inline-block; margin-right: 0.5rem;">
                          <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                          <option value="Confirmed" <?= $row['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                          <option value="Completed" <?= $row['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                          <option value="Cancelled" <?= $row['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-success">Update</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info">No appointments found.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Dark Mode Toggle
    const toggleBtn = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const label = document.getElementById('themeLabel');

    const prefersDark = localStorage.getItem('dark-mode') === 'true';
    
    if (prefersDark) {
      document.body.classList.add('dark-mode');
      label.textContent = 'Dark Mode';
    }

    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      const isDark = document.body.classList.contains('dark-mode');
      localStorage.setItem('dark-mode', isDark);
      label.textContent = isDark ? 'Dark Mode' : 'Light Mode';
    });

    // Tab Switching
    const dashboardTab = document.getElementById('dashboardTab');
    const bookingsTab = document.getElementById('bookingsTab');
    const dashboardContent = document.getElementById('dashboardContent');
    const bookingsContent = document.getElementById('bookingsContent');

    dashboardTab.addEventListener('click', () => {
      dashboardTab.classList.add('active');
      bookingsTab.classList.remove('active');
      dashboardContent.style.display = 'block';
      bookingsContent.style.display = 'none';
    });

    bookingsTab.addEventListener('click', () => {
      bookingsTab.classList.add('active');
      dashboardTab.classList.remove('active');
      bookingsContent.style.display = 'block';
      dashboardContent.style.display = 'none';
    });
  </script>
</body>
</html>