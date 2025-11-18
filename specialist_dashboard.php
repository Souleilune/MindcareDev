<?php
session_start();
include 'supabase.php';

// Restrict access to specialists only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Specialist') {
  echo "<script>alert('Access denied.'); window.location.href='login.php';</script>";
  exit;
}

$specialist_id = $_SESSION['user']['id'];

// Fetch appointments for this specialist
$stmt = $conn->prepare("
  SELECT 
    a.id AS appointment_id,
    u.fullname AS patient_name,
    a.appointment_date,
    a.appointment_time,
    a.status
  FROM appointments a
  JOIN users u ON a.user_id = u.id
  WHERE a.specialist_id = ?
  ORDER BY a.appointment_date, a.appointment_time
");
$stmt->bind_param("i", $specialist_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Specialist Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
  <h3>👨‍⚕️ Welcome, <?= htmlspecialchars($_SESSION['user']['fullname']) ?></h3>
  <p class="text-muted">Here are your upcoming appointments:</p>

  <table class="table table-bordered table-striped mt-4">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Patient</th>
        <th>Date</th>
        <th>Time</th>
        <th>Status</th>
        <th>Update</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['appointment_id'] ?></td>
          <td><?= htmlspecialchars($row['patient_name']) ?></td>
          <td><?= date('F j, Y', strtotime($row['appointment_date'])) ?></td>
          <td><?= date('g:i A', strtotime($row['appointment_time'])) ?></td>
          <td><?= $row['status'] ?></td>
          <td>
            <form method="POST" action="update_status.php" class="d-flex">
              <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
              <select name="status" class="form-select me-2">
                <option value="Confirmed" <?= $row['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="Completed" <?= $row['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option value="Cancelled" <?= $row['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
              <button type="submit" class="btn btn-sm btn-primary">Update</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <a href="logout.php" class="btn btn-outline-danger mt-3">Logout</a>
</body>
</html>