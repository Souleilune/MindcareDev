<?php
session_start();
include 'db.php';

// Restrict access to specialists only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Specialist') {
  echo "<script>alert('Access denied.'); window.location.href='login.php';</script>";
  exit;
}

$specialist_id = $_SESSION['user']['id'];
$specialist_name = $_SESSION['user']['fullname'];

// Fetch recent bookings (last 7 days)
$stmt_recent = $conn->prepare("
  SELECT 
    a.id AS appointment_id,
    u.fullname AS patient_name,
    a.appointment_date,
    a.appointment_time,
    a.status,
    a.created_at
  FROM appointments a
  JOIN users u ON a.user_id = u.id
  WHERE a.specialist_id = ?
  AND a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
  ORDER BY a.created_at DESC
  LIMIT 10
");
$stmt_recent->bind_param("i", $specialist_id);
$stmt_recent->execute();
$recent_bookings = $stmt_recent->get_result();

// Fetch all appointments for booking management
$stmt_all = $conn->prepare("
  SELECT 
    a.id AS appointment_id,
    u.fullname AS patient_name,
    u.email AS patient_email,
    u.gender AS patient_gender,
    a.appointment_date,
    a.appointment_time,
    a.status,
    a.notes,
    a.created_at
  FROM appointments a
  JOIN users u ON a.user_id = u.id
  WHERE a.specialist_id = ?
  ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt_all->bind_param("i", $specialist_id);
$stmt_all->execute();
$all_appointments = $stmt_all->get_result();

// Get statistics
$stmt_stats = $conn->prepare("
  SELECT 
    COUNT(*) as total_appointments,
    SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
  FROM appointments
  WHERE specialist_id = ?
");
$stmt_stats->bind_param("i", $specialist_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Specialist Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
 
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
          <h3>Welcome, <?= htmlspecialchars($specialist_name) ?></h3>
          <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-12">
        <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab">
              Dashboard
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">
              Booking Management
            </button>
          </li>
        </ul>

        <div class="tab-content" id="dashboardTabsContent">
          <!-- Dashboard Tab -->
          <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
            <div class="p-3">
              <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <?= htmlspecialchars($_GET['success']) ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
              <?php endif; ?>

              <h5>Statistics Overview</h5>
              <div class="row mt-3">
                <div class="col-md-3">
                  <div class="card">
                    <div class="card-body">
                      <small class="text-muted">Total Appointments</small>
                      <h4><?= $stats['total_appointments'] ?></h4>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card">
                    <div class="card-body">
                      <small class="text-muted">Confirmed</small>
                      <h4><?= $stats['confirmed'] ?></h4>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card">
                    <div class="card-body">
                      <small class="text-muted">Pending</small>
                      <h4><?= $stats['pending'] ?></h4>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card">
                    <div class="card-body">
                      <small class="text-muted">Completed</small>
                      <h4><?= $stats['completed'] ?></h4>
                    </div>
                  </div>
                </div>
              </div>

              <h5 class="mt-4">Recent Bookings (Last 7 Days)</h5>
              <?php if ($recent_bookings->num_rows > 0): ?>
                <div class="table-responsive mt-3">
                  <table class="table table-bordered table-hover">
                    <thead class="table-light">
                      <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Appointment Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Booked On</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $recent_bookings->fetch_assoc()): ?>
                        <tr>
                          <td><?= $row['appointment_id'] ?></td>
                          <td><?= htmlspecialchars($row['patient_name']) ?></td>
                          <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                          <td><?= date('g:i A', strtotime($row['appointment_time'])) ?></td>
                          <td>
                            <span class="badge bg-<?= 
                              $row['status'] === 'Confirmed' ? 'success' : 
                              ($row['status'] === 'Pending' ? 'warning' : 
                              ($row['status'] === 'Completed' ? 'info' : 'danger')) 
                            ?>">
                              <?= $row['status'] ?>
                            </span>
                          </td>
                          <td><?= date('M d, Y g:i A', strtotime($row['created_at'])) ?></td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="alert alert-info mt-3">No recent bookings in the last 7 days.</div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Booking Management Tab -->
          <div class="tab-pane fade" id="bookings" role="tabpanel">
            <div class="p-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>All Appointments</h5>
                <button class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">Refresh</button>
              </div>

              <?php if ($all_appointments->num_rows > 0): ?>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <thead class="table-light">
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
                      <?php 
                      $all_appointments->data_seek(0);
                      while ($row = $all_appointments->fetch_assoc()): 
                      ?>
                        <tr>
                          <td><?= $row['appointment_id'] ?></td>
                          <td>
                            <?= htmlspecialchars($row['patient_name']) ?>
                            <?php if ($row['patient_gender']): ?>
                              <br><small class="text-muted"><?= $row['patient_gender'] ?></small>
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars($row['patient_email']) ?></td>
                          <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                          <td><?= date('g:i A', strtotime($row['appointment_time'])) ?></td>
                          <td>
                            <span class="badge bg-<?= 
                              $row['status'] === 'Confirmed' ? 'success' : 
                              ($row['status'] === 'Pending' ? 'warning' : 
                              ($row['status'] === 'Completed' ? 'info' : 'danger')) 
                            ?>">
                              <?= $row['status'] ?>
                            </span>
                          </td>
                          <td><?= htmlspecialchars($row['notes'] ?? 'N/A') ?></td>
                          <td>
                            <form method="POST" action="update_status.php" class="d-flex gap-1" onsubmit="return confirm('Are you sure you want to update this appointment status?');">
                              <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                              <select name="status" class="form-select form-select-sm" required>
                                <option value="">Select Status...</option>
                                <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Confirmed" <?= $row['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="Completed" <?= $row['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Cancelled" <?= $row['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                              </select>
                              <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            </form>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="alert alert-info">No appointments found.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>