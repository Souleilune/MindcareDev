<?php
/**
 * DIAGNOSTIC SCRIPT FOR SPECIALIST DASHBOARD
 * This script will help identify why appointments are not showing
 * 
 * Usage: Upload to your server and access via browser
 * URL: http://yoursite.com/test-specialist-data.php
 */

session_start();
include 'supabase.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Specialist Dashboard Diagnostic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f5f5f5; padding: 20px; }
    .test-section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { color: #28a745; }
    .error { color: #dc3545; }
    .warning { color: #ffc107; }
    pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="mb-4">🔍 Specialist Dashboard Diagnostic</h1>

    <!-- TEST 1: Session Check -->
    <div class="test-section">
      <h4>✅ Test 1: Session & Authentication</h4>
      <?php
      if (!isset($_SESSION['user'])) {
        echo "<p class='error'>❌ No user logged in. Please login first.</p>";
        echo "<a href='admin_login.php' class='btn btn-primary'>Login as Specialist</a>";
      } else {
        echo "<p class='success'>✅ User is logged in</p>";
        echo "<pre>" . print_r($_SESSION['user'], true) . "</pre>";
        
        if ($_SESSION['user']['role'] !== 'Specialist') {
          echo "<p class='warning'>⚠️ Warning: User role is '" . $_SESSION['user']['role'] . "' (should be 'Specialist')</p>";
        } else {
          echo "<p class='success'>✅ Role is 'Specialist'</p>";
        }
      }
      ?>
    </div>

    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'Specialist'): ?>
      <?php
      $specialist_id = $_SESSION['user']['id'];
      $specialist_name = $_SESSION['user']['fullname'];
      ?>

      <!-- TEST 2: Direct Appointments Query (No Join) -->
      <div class="test-section">
        <h4>✅ Test 2: Basic Appointments Query (No Foreign Key)</h4>
        <?php
        echo "<p>Querying: <code>appointments WHERE specialist_id = {$specialist_id}</code></p>";
        
        $basicAppointments = supabaseSelect(
          'appointments',
          ['specialist_id' => $specialist_id],
          'id,user_id,specialist_id,appointment_date,appointment_time,status,created_at',
          'created_at.desc'
        );
        
        if (empty($basicAppointments)) {
          echo "<p class='error'>❌ No appointments found for specialist_id = {$specialist_id}</p>";
          echo "<p>This means either:</p>";
          echo "<ul>";
          echo "<li>No appointments have been booked with this specialist</li>";
          echo "<li>The specialist_id doesn't match any records</li>";
          echo "<li>Row Level Security (RLS) is blocking access</li>";
          echo "</ul>";
        } else {
          echo "<p class='success'>✅ Found " . count($basicAppointments) . " appointment(s)</p>";
          echo "<pre>" . print_r($basicAppointments, true) . "</pre>";
        }
        ?>
      </div>

      <!-- TEST 3: Appointments Query WITH Foreign Key (users!user_id) -->
      <div class="test-section">
        <h4>✅ Test 3: Appointments Query WITH Foreign Key (users!user_id)</h4>
        <?php
        echo "<p>Querying: <code>appointments WITH users!user_id(fullname,email)</code></p>";
        
        $joinedAppointments = supabaseSelect(
          'appointments',
          ['specialist_id' => $specialist_id],
          'id,user_id,appointment_date,appointment_time,status,created_at,users!user_id(fullname,email,gender)',
          'created_at.desc'
        );
        
        if (empty($joinedAppointments)) {
          echo "<p class='error'>❌ Query with foreign key returned empty</p>";
        } else {
          echo "<p class='success'>✅ Found " . count($joinedAppointments) . " appointment(s) with user data</p>";
          echo "<pre>" . print_r($joinedAppointments, true) . "</pre>";
          
          // Check if user data is actually populated
          $firstAppointment = $joinedAppointments[0];
          if (isset($firstAppointment['users']) && !empty($firstAppointment['users'])) {
            echo "<p class='success'>✅ User data is populated correctly</p>";
          } else {
            echo "<p class='error'>❌ User data is NOT populated (foreign key might not be working)</p>";
          }
        }
        ?>
      </div>

      <!-- TEST 4: All Users Table -->
      <div class="test-section">
        <h4>✅ Test 4: Users Table Check</h4>
        <?php
        $allUsers = supabaseSelect('users', [], 'id,fullname,email,role', 'created_at.desc', 10);
        
        if (empty($allUsers)) {
          echo "<p class='error'>❌ No users found in database</p>";
        } else {
          echo "<p class='success'>✅ Found " . count($allUsers) . " users</p>";
          echo "<table class='table table-sm table-bordered'>";
          echo "<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr></thead>";
          echo "<tbody>";
          foreach ($allUsers as $user) {
            $highlight = ($user['id'] == $specialist_id) ? 'background: #fff3cd;' : '';
            echo "<tr style='$highlight'>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['fullname']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "</tr>";
          }
          echo "</tbody></table>";
          echo "<p><small>Your specialist account is highlighted in yellow</small></p>";
        }
        ?>
      </div>

      <!-- TEST 5: All Appointments (To See Total Data) -->
      <div class="test-section">
        <h4>✅ Test 5: All Appointments in Database</h4>
        <?php
        $allAppointments = supabaseSelect('appointments', [], '*', 'created_at.desc', 20);
        
        if (empty($allAppointments)) {
          echo "<p class='error'>❌ No appointments exist in the database at all</p>";
          echo "<p>This means no bookings have been created yet. Try creating a test booking first.</p>";
        } else {
          echo "<p class='success'>✅ Found " . count($allAppointments) . " total appointments in database</p>";
          echo "<table class='table table-sm table-bordered'>";
          echo "<thead><tr><th>ID</th><th>Patient ID</th><th>Specialist ID</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>";
          echo "<tbody>";
          foreach ($allAppointments as $apt) {
            $highlight = ($apt['specialist_id'] == $specialist_id) ? 'background: #d4edda;' : '';
            echo "<tr style='$highlight'>";
            echo "<td>" . $apt['id'] . "</td>";
            echo "<td>" . $apt['user_id'] . "</td>";
            echo "<td>" . $apt['specialist_id'] . "</td>";
            echo "<td>" . $apt['appointment_date'] . "</td>";
            echo "<td>" . $apt['appointment_time'] . "</td>";
            echo "<td>" . $apt['status'] . "</td>";
            echo "</tr>";
          }
          echo "</tbody></table>";
          echo "<p><small>Appointments for your specialist_id ({$specialist_id}) are highlighted in green</small></p>";
        }
        ?>
      </div>

      <!-- TEST 6: Supabase RLS Check -->
      <div class="test-section">
        <h4>✅ Test 6: Row Level Security (RLS) Status</h4>
        <p>Checking if RLS might be blocking queries...</p>
        <?php
        // Try with RLS bypass
        $bypassTest = supabaseSelect(
          'appointments',
          ['specialist_id' => $specialist_id],
          '*',
          'created_at.desc',
          5,
          true // Bypass RLS
        );
        
        echo "<p><strong>Query WITH RLS bypass (using SERVICE_KEY):</strong></p>";
        if (empty($bypassTest)) {
          echo "<p class='error'>❌ Still no results even with RLS bypass</p>";
          echo "<p>This suggests the specialist_id doesn't match any records, not an RLS issue.</p>";
        } else {
          echo "<p class='success'>✅ Found " . count($bypassTest) . " appointments with RLS bypass</p>";
          echo "<pre>" . print_r($bypassTest, true) . "</pre>";
        }
        ?>
      </div>

      <!-- TEST 7: Create Test Appointment -->
      <div class="test-section">
        <h4>✅ Test 7: Create Test Appointment</h4>
        <p>Let's create a test appointment to verify the system works:</p>
        <form method="POST" action="">
          <input type="hidden" name="action" value="create_test_appointment">
          <button type="submit" class="btn btn-primary">Create Test Appointment</button>
        </form>

        <?php
        if (isset($_POST['action']) && $_POST['action'] === 'create_test_appointment') {
          // Find any patient user
          $patients = supabaseSelect('users', ['role' => 'Patient'], 'id', null, 1);
          
          if (empty($patients)) {
            echo "<p class='error'>❌ No patient users found. Need at least one patient to create test appointment.</p>";
          } else {
            $patient_id = $patients[0]['id'];
            
            $testAppointment = supabaseInsert('appointments', [
              'user_id' => $patient_id,
              'specialist_id' => $specialist_id,
              'appointment_date' => date('Y-m-d', strtotime('+1 day')),
              'appointment_time' => '14:00:00',
              'status' => 'Pending',
              'notes' => 'Test appointment created by diagnostic script'
            ]);
            
            if (isset($testAppointment['error'])) {
              echo "<p class='error'>❌ Failed to create test appointment</p>";
              echo "<pre>" . print_r($testAppointment, true) . "</pre>";
            } else {
              echo "<p class='success'>✅ Test appointment created successfully!</p>";
              echo "<pre>" . print_r($testAppointment, true) . "</pre>";
              echo "<p><a href='specialist_dashboard.php' class='btn btn-success'>Go to Specialist Dashboard</a></p>";
            }
          }
        }
        ?>
      </div>

      <!-- Recommendations -->
      <div class="test-section">
        <h4>📋 Diagnosis & Recommendations</h4>
        <?php
        $issues = [];
        $recommendations = [];
        
        // Check basic query
        if (empty($basicAppointments)) {
          $issues[] = "No appointments found for specialist_id = {$specialist_id}";
          $recommendations[] = "Create a test booking from the patient side (book_appointment.php)";
          $recommendations[] = "Verify the specialist_id matches in both the session and database";
        }
        
        // Check foreign key query
        if (!empty($joinedAppointments) && (!isset($joinedAppointments[0]['users']) || empty($joinedAppointments[0]['users']))) {
          $issues[] = "Foreign key relationship (users!user_id) is not populating data";
          $recommendations[] = "Check if foreign key constraint exists in Supabase";
          $recommendations[] = "Verify the 'users' table has matching user_id records";
        }
        
        if (empty($issues)) {
          echo "<p class='success'>✅ No issues detected! Appointments should be showing on the dashboard.</p>";
          echo "<p>If they're still not showing, try:</p>";
          echo "<ul>";
          echo "<li>Clear browser cache and refresh</li>";
          echo "<li>Check browser console for JavaScript errors</li>";
          echo "<li>Verify specialist_dashboard.php is using the corrected code</li>";
          echo "</ul>";
        } else {
          echo "<p class='error'><strong>Issues Found:</strong></p>";
          echo "<ul>";
          foreach ($issues as $issue) {
            echo "<li>" . $issue . "</li>";
          }
          echo "</ul>";
          
          echo "<p class='warning'><strong>Recommendations:</strong></p>";
          echo "<ol>";
          foreach ($recommendations as $rec) {
            echo "<li>" . $rec . "</li>";
          }
          echo "</ol>";
        }
        ?>
      </div>

    <?php endif; ?>

    <!-- Navigation -->
    <div class="test-section">
      <h4>🔗 Quick Links</h4>
      <a href="specialist_dashboard.php" class="btn btn-primary me-2">Go to Specialist Dashboard</a>
      <a href="book_appointment.php" class="btn btn-secondary me-2">Book Appointment (Patient Side)</a>
      <a href="admin_login.php" class="btn btn-info me-2">Login as Different Specialist</a>
      <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-success">Refresh This Page</a>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>