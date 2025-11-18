<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}
include 'db.php';

$user_id = $_SESSION['user']['id'];
$user_name = $_SESSION['user']['fullname'] ?? 'User';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = trim($_POST['fullname']);
  $gender = $_POST['gender'];
  $role = $_POST['role'];

  $stmt = $conn->prepare("UPDATE users SET fullname = ?, gender = ?, role = ? WHERE id = ?");
  $stmt->bind_param("sssi", $fullname, $gender, $role, $user_id);

  if ($stmt->execute()) {
    $_SESSION['user']['fullname'] = $fullname;
    $_SESSION['user']['gender'] = $gender;
    $_SESSION['user']['role'] = $role;

    header("Location: profile.php?success=Profile updated");
    exit;
  } else {
    $error = "Failed to update profile.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Profile - MindCare</title>
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

    /* Dark Mode Variables - these colors replace light mode colors when dark mode is active */
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
      display: flex;
      justify-content: center;
      align-items: flex-start;
    }

    .content-inner {
      max-width: 800px;
      width: 100%;
    }

    /* Header */
    .page-header {
      margin-bottom: 2rem;
    }

    .page-header h1 {
      font-size: 2rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
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

    .alert-danger {
      background-color: rgba(239, 83, 80, 0.1);
      color: #c62828;
    }

    body.dark-mode .alert-danger {
      background-color: rgba(239, 83, 80, 0.2);
      color: #ef5350;
    }

    /* Form Card */
    .form-card {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    body.dark-mode .form-card {
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    /* Form Elements */
    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-label {
      display: block;
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      font-size: 0.95rem;
      background: var(--bg-light);
      color: var(--text-dark);
      transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
      outline: none;
      border-color: var(--primary-teal);
      box-shadow: 0 0 0 3px rgba(90, 208, 190, 0.1);
      background: var(--card-bg);
    }

    /* Dark mode specific styling for form inputs */
    body.dark-mode .form-control,
    body.dark-mode .form-select {
      background: #1a1a1a;
      border-color: var(--border-color);
    }

    body.dark-mode .form-control:focus,
    body.dark-mode .form-select:focus {
      background: #2a2a2a;
    }

    /* Buttons */
    .button-group {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
      padding-top: 2rem;
      border-top: 1px solid var(--border-color);
    }

    .btn-save {
      background: linear-gradient(135deg, var(--primary-teal) 0%, var(--primary-teal-dark) 100%);
      color: white;
      border: none;
      padding: 0.875rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.95rem;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(90, 208, 190, 0.3);
      transition: all 0.3s ease;
    }

    .btn-save:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(90, 208, 190, 0.4);
    }

    .btn-cancel {
      background: transparent;
      border: 1px solid var(--border-color);
      color: var(--text-muted);
      padding: 0.875rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.95rem;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .btn-cancel:hover {
      background: var(--bg-light);
      border-color: var(--primary-teal);
      color: var(--primary-teal);
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

      .button-group {
        flex-direction: column;
      }

      .btn-save,
      .btn-cancel {
        width: 100%;
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
      <a class="nav-link" href="appointments.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
        MY APPOINTMENTS
      </a>
      <a class="nav-link active" href="profile.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        PROFILE
      </a>
      <a class="nav-link" href="faq.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        FAQS
      </a>
    </nav>

    <!-- Dark Mode Toggle Button -->
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
        <h1>Edit Profile</h1>
        <p class="subtitle">Update your personal information</p>
      </div>

      <!-- Error Alert -->
      <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Form Card -->
      <div class="form-card">
        <form method="POST">
          <div class="form-group">
            <label for="fullname" class="form-label">Full Name</label>
            <input 
              type="text" 
              id="fullname"
              name="fullname" 
              class="form-control" 
              value="<?= htmlspecialchars($_SESSION['user']['fullname']) ?>" 
              placeholder="Enter your full name"
              required 
            />
          </div>

          <div class="form-group">
            <label for="gender" class="form-label">Gender</label>
            <select id="gender" name="gender" class="form-select" required>
              <option value="">Select gender</option>
              <option value="Male" <?= $_SESSION['user']['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= $_SESSION['user']['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
              <option value="Other" <?= $_SESSION['user']['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
          </div>

          <div class="form-group">
            <label for="role" class="form-label">Role</label>
            <select id="role" name="role" class="form-select" required>
              <option value="">Select role</option>
              <option value="Patient" <?= $_SESSION['user']['role'] === 'Patient' ? 'selected' : '' ?>>Patient</option>
              <option value="Specialist" <?= $_SESSION['user']['role'] === 'Specialist' ? 'selected' : '' ?>>Specialist</option>
            </select>
          </div>

          <div class="button-group">
            <button type="submit" class="btn-save">Save Changes</button>
            <a href="profile.php" class="btn-cancel">Cancel</a>
          </div>
        </form>
      </div>

    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Dark Mode Toggle Functionality
    // This script handles switching between light and dark color schemes
    
    // Get references to the toggle button and its visual elements
    const toggleBtn = document.getElementById('themeToggle'); // The clickable button
    const icon = document.getElementById('themeIcon'); // The emoji icon (sun/moon)
    const label = document.getElementById('themeLabel'); // The text label

    // Check if user previously saved a theme preference in browser's localStorage
    // localStorage is a way to save data that persists even after closing the browser
    const prefersDark = localStorage.getItem('dark-mode') === 'true';
    
    // If user prefers dark mode, apply it immediately when page loads
    if (prefersDark) {
      document.body.classList.add('dark-mode'); // Add CSS class that triggers dark colors
      icon.textContent = '🌙'; // Change icon to moon
      label.textContent = 'Dark Mode'; // Update label
    }

    // Add click event listener to the toggle button
    toggleBtn.addEventListener('click', () => {
      // Toggle the 'dark-mode' class on the body element
      // If it's there, remove it. If it's not there, add it.
      document.body.classList.toggle('dark-mode');
      
      // Check if dark mode is now active after the toggle
      const isDark = document.body.classList.contains('dark-mode');
      
      // Save the user's preference to localStorage
      // This way, their choice persists across page visits and browser sessions
      localStorage.setItem('dark-mode', isDark);
      
      // Add a smooth rotation animation to the icon for visual feedback
      icon.style.transform = 'rotate(360deg)'; // Spin 360 degrees
      setTimeout(() => icon.style.transform = 'rotate(0deg)', 500); // Reset after 500ms
      
      // Update the icon and label text based on the current theme
      // Ternary operator: if isDark is true, use moon icon, else use sun icon
      icon.textContent = isDark ? '🌙' : '🌞';
      label.textContent = isDark ? 'Dark Mode' : 'Light Mode';
    });

    // Add CSS transition property to make the icon rotation smooth
    icon.style.transition = 'transform 0.5s ease';
  </script>
</body>
</html>