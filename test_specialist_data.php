<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Patient Appointments Debug</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 20px; background: #f8f9fa; }
    .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { color: #28a745; }
    .error { color: #dc3545; }
    .warning { color: #ffc107; }
    pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Patient Appointments Debugging Tool</h1>
    <p class="text-muted">Testing why patient appointments aren't showing</p>

    <?php
    session_start();
    require_once 'supabase.php';

    // Check if user is logged in
    if (!isset($_SESSION['user'])) {
      echo "<div class='alert alert-danger'>❌ No user logged in. Please login first.</div>";
      echo "<a href='login.php' class='btn btn-primary'>Go to Login</a>";
      exit;
    }

    $user_id = $_SESSION['user']['id'];
    $user_name = $_SESSION['user']['fullname'];
    $user_role = $_SESSION['user']['role'];

    echo "<div class='test-section'>";
    echo "<h3>🔍 Test 1: Session Information</h3>";
    echo "<p><strong>Logged in as:</strong> $user_name (ID: $user_id)</p>";
    echo "<p><strong>Role:</strong> $user_role</p>";
    
    if ($user_role !== 'Patient') {
      echo "<p class='warning'>⚠️ Warning: You are logged in as '$user_role', not 'Patient'. This page is for testing patient appointments.</p>";
    }
    echo "</div>";

    // Test 2: Basic appointments query (No foreign key)
    echo "<div class='test-section'>";
    echo "<h3>✅ Test 2: Basic Appointments Query (No Foreign Key)</h3>";
    echo "<p>Querying: <code>appointments WHERE user_id = $user_id</code></p>";
    
    $basicAppointments = supabaseSelect(
      'appointments',
      ['user_id' => $user_id],
      'id,user_id,specialist_id,appointment_date,appointment_time,status,created_at',
      'appointment_date.desc',
      null,
      true  // Bypass RLS
    );
    
    if (empty($basicAppointments)) {
      echo "<p class='error'>❌ No appointments found for user_id = $user_id</p>";
      echo "<p>This means:</p>";
      echo "<ul>";
      echo "<li>You haven't created any appointments yet, OR</li>";
      echo "<li>Row Level Security (RLS) is blocking access, OR</li>";
      echo "<li>The user_id in the session doesn't match the database</li>";
      echo "</ul>";
    } else {
      echo "<p class='success'>✅ Found " . count($basicAppointments) . " appointment(s)</p>";
      echo "<pre>" . json_encode($basicAppointments, JSON_PRETTY_PRINT) . "</pre>";
    }
    echo "</div>";

    // Test 3: Query WITH foreign key (different syntaxes)
    echo "<div class='test-section'>";
    echo "<h3>🔗 Test 3: Appointments Query WITH Foreign Key</h3>";
    
    // Try method 1: Using constraint name
    echo "<p><strong>Method 1:</strong> Using constraint name <code>users!appointments_specialist_id_fkey</code></p>";
    $appointmentsMethod1 = supabaseSelect(
      'appointments',
      ['user_id' => $user_id],
      'id,specialist_id,appointment_date,appointment_time,status,users!appointments_specialist_id_fkey(fullname,email)',
      'appointment_date.desc',
      null,
      true
    );
    
    if (!empty($appointmentsMethod1) && isset($appointmentsMethod1[0]['users'])) {
      echo "<p class='success'>✅ Method 1 worked!</p>";
      echo "<pre>" . json_encode($appointmentsMethod1, JSON_PRETTY_PRINT) . "</pre>";
    } else {
      echo "<p class='error'>❌ Method 1 failed</p>";
      
      // Try method 2: Short syntax
      echo "<p><strong>Method 2:</strong> Using short syntax <code>users:specialist_id</code></p>";
      $appointmentsMethod2 = supabaseSelect(
        'appointments',
        ['user_id' => $user_id],
        'id,specialist_id,appointment_date,appointment_time,status,users:specialist_id(fullname,email)',
        'appointment_date.desc',
        null,
        true
      );
      
      if (!empty($appointmentsMethod2) && isset($appointmentsMethod2[0]['users'])) {
        echo "<p class='success'>✅ Method 2 worked!</p>";
        echo "<pre>" . json_encode($appointmentsMethod2, JSON_PRETTY_PRINT) . "</pre>";
      } else {
        echo "<p class='error'>❌ Method 2 also failed</p>";
        echo "<p class='warning'>⚠️ Foreign key relationship is not working. Will need to use fallback method (separate queries).</p>";
      }
    }
    echo "</div>";

    // Test 4: Check all appointments in database
    echo "<div class='test-section'>";
    echo "<h3>📊 Test 4: All Appointments in Database</h3>";
    
    $allAppointments = supabaseSelect(
      'appointments',
      [],
      'id,user_id,specialist_id,appointment_date,appointment_time,status',
      'created_at.desc',
      20,
      true
    );
    
    if (!empty($allAppointments)) {
      echo "<p class='success'>✅ Found " . count($allAppointments) . " total appointments in database</p>";
      
      // Check if any belong to current user
      $userAppointments = array_filter($allAppointments, function($apt) use ($user_id) {
        return $apt['user_id'] == $user_id;
      });
      
      if (!empty($userAppointments)) {
        echo "<p class='success'>✅ Found " . count($userAppointments) . " appointment(s) belonging to you (user_id=$user_id)</p>";
      } else {
        echo "<p class='warning'>⚠️ None of these appointments belong to you (user_id=$user_id)</p>";
      }
      
      echo "<table class='table table-sm table-bordered'>";
      echo "<thead><tr><th>ID</th><th>Patient ID</th><th>Specialist ID</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>";
      echo "<tbody>";
      foreach ($allAppointments as $apt) {
        $highlight = ($apt['user_id'] == $user_id) ? " style='background-color: #fff3cd;'" : "";
        echo "<tr$highlight>";
        echo "<td>" . $apt['id'] . "</td>";
        echo "<td>" . $apt['user_id'] . "</td>";
        echo "<td>" . $apt['specialist_id'] . "</td>";
        echo "<td>" . $apt['appointment_date'] . "</td>";
        echo "<td>" . $apt['appointment_time'] . "</td>";
        echo "<td>" . $apt['status'] . "</td>";
        echo "</tr>";
      }
      echo "</tbody></table>";
      echo "<p class='text-muted'><small>Your appointments are highlighted in yellow</small></p>";
    } else {
      echo "<p class='error'>❌ No appointments found in the entire database</p>";
    }
    echo "</div>";

    // Test 5: Check specialists table
    echo "<div class='test-section'>";
    echo "<h3>👨‍⚕️ Test 5: Available Specialists</h3>";
    
    $specialists = supabaseSelect(
      'users',
      ['role' => 'Specialist'],
      'id,fullname,email',
      'created_at.desc',
      null,
      true
    );
    
    if (!empty($specialists)) {
      echo "<p class='success'>✅ Found " . count($specialists) . " specialist(s)</p>";
      echo "<table class='table table-sm table-bordered'>";
      echo "<thead><tr><th>ID</th><th>Name</th><th>Email</th></tr></thead>";
      echo "<tbody>";
      foreach ($specialists as $spec) {
        echo "<tr>";
        echo "<td>" . $spec['id'] . "</td>";
        echo "<td>" . htmlspecialchars($spec['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($spec['email']) . "</td>";
        echo "</tr>";
      }
      echo "</tbody></table>";
    }
    echo "</div>";

    // Diagnosis
    echo "<div class='test-section'>";
    echo "<h3>📋 Diagnosis & Recommendations</h3>";
    
    echo "<h5>Issues Found:</h5>";
    echo "<ul>";
    
    if (empty($basicAppointments)) {
      echo "<li class='error'>No appointments exist for your user_id ($user_id)</li>";
      echo "<ul>";
      echo "<li>Try creating a new appointment from <a href='book_appointment.php'>Book Appointment</a> page</li>";
      echo "<li>Verify you're logged in with the correct account</li>";
      echo "</ul>";
    } else {
      echo "<li class='success'>Appointments exist for your account</li>";
      
      if (empty($appointmentsMethod1) || !isset($appointmentsMethod1[0]['users'])) {
        echo "<li class='error'>Foreign key JOIN is failing</li>";
        echo "<ul><li>The query can find appointments but can't join with specialists</li></ul>";
      }
    }
    
    echo "</ul>";
    
    echo "<h5>Solution:</h5>";
    echo "<p>Use the FIXED appointments.php file which implements:</p>";
    echo "<ol>";
    echo "<li>Multiple foreign key syntax attempts</li>";
    echo "<li>Fallback method: Fetch appointments and specialists separately, then merge</li>";
    echo "<li>RLS bypass using SERVICE_KEY</li>";
    echo "</ol>";
    echo "</div>";
    ?>

    <div class="test-section">
      <h3>🔧 Next Steps</h3>
      <ol>
        <li>If Test 2 shows no appointments: Create a test appointment from <a href="book_appointment.php">Book Appointment</a></li>
        <li>If Test 2 shows appointments but Test 3 fails: Use the fixed appointments.php file</li>
        <li>Replace your current appointments.php with appointments_FIXED.php</li>
        <li>Test again by logging in as a patient and viewing appointments</li>
      </ol>
      
      <a href="appointments.php" class="btn btn-primary">View Appointments Page</a>
      <a href="book_appointment.php" class="btn btn-success">Book New Appointment</a>
    </div>

  </div>
</body>
</html>