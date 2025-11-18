<?php
session_start();
require_once 'supabase.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
  echo "❌ No user logged in. Please <a href='login.php'>login first</a>.<br>";
  exit;
}

$user_id = $_SESSION['user']['id'];
$user_name = $_SESSION['user']['fullname'];

echo "<h1>🔍 Profile & Appointments Diagnostic Tool</h1>";
echo "<style>
  body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
  .section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
  .success { color: #28a745; font-weight: bold; }
  .error { color: #dc3545; font-weight: bold; }
  .warning { color: #ffc107; font-weight: bold; }
  pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
  table { width: 100%; border-collapse: collapse; margin: 10px 0; }
  th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
  th { background: #f8f9fa; font-weight: bold; }
</style>";

echo "<div class='section'>";
echo "<h2>👤 Current Session User</h2>";
echo "<p><strong>User ID:</strong> $user_id</p>";
echo "<p><strong>Name:</strong> $user_name</p>";
echo "<p><strong>Role:</strong> " . ($_SESSION['user']['role'] ?? 'N/A') . "</p>";
echo "</div>";

// TEST 1: Check users table structure
echo "<div class='section'>";
echo "<h2>📋 Test 1: Users Table Structure</h2>";
echo "<p>Fetching your user record to see what columns exist...</p>";

$userRecord = supabaseSelect('users', ['id' => $user_id], '*', null, 1, true);

if (!empty($userRecord)) {
  $user = $userRecord[0];
  echo "<p class='success'>✅ User record found</p>";
  echo "<p><strong>Available columns:</strong></p>";
  echo "<table>";
  echo "<tr><th>Column</th><th>Value</th><th>Status</th></tr>";
  
  $requiredColumns = [
    'id', 'fullname', 'email', 'role', 'age', 'gender',
    'phone', 'address', 'height', 'weight', 'blood_group', 'bmi',
    'emergency_contact_name', 'emergency_contact_relationship', 'emergency_contact_phone'
  ];
  
  foreach ($requiredColumns as $col) {
    $exists = array_key_exists($col, $user);
    $value = $exists ? ($user[$col] ?? 'NULL') : 'COLUMN MISSING';
    $status = $exists ? "<span class='success'>EXISTS</span>" : "<span class='error'>MISSING</span>";
    
    if ($exists && empty($user[$col]) && $user[$col] !== 0) {
      $status = "<span class='warning'>EMPTY</span>";
    }
    
    echo "<tr>";
    echo "<td><code>$col</code></td>";
    echo "<td>" . htmlspecialchars(is_array($value) ? json_encode($value) : (string)$value) . "</td>";
    echo "<td>$status</td>";
    echo "</tr>";
  }
  
  echo "</table>";
  
  // Show all columns that exist
  echo "<p><strong>All columns in database:</strong></p>";
  echo "<pre>" . json_encode(array_keys($user), JSON_PRETTY_PRINT) . "</pre>";
  
} else {
  echo "<p class='error'>❌ Could not fetch user record</p>";
}
echo "</div>";

// TEST 2: Check appointments table structure and data
echo "<div class='section'>";
echo "<h2>🗓️ Test 2: Appointments for User</h2>";

$appointments = supabaseSelect(
  'appointments',
  ['user_id' => $user_id],
  'id,user_id,specialist_id,appointment_date,appointment_time,status,created_at',
  'appointment_date.desc',
  null,
  true
);

if (empty($appointments)) {
  echo "<p class='error'>❌ No appointments found for user_id = $user_id</p>";
  echo "<p><strong>Possible reasons:</strong></p>";
  echo "<ul>";
  echo "<li>You haven't created any appointments yet</li>";
  echo "<li>Row Level Security (RLS) is blocking access</li>";
  echo "<li>The user_id doesn't match any appointments</li>";
  echo "</ul>";
  echo "<p>Try creating a test appointment from <a href='book_appointment.php'>Book Appointment</a></p>";
} else {
  echo "<p class='success'>✅ Found " . count($appointments) . " appointment(s)</p>";
  echo "<table>";
  echo "<tr><th>ID</th><th>Specialist ID</th><th>Date</th><th>Time</th><th>Status</th></tr>";
  
  foreach ($appointments as $apt) {
    echo "<tr>";
    echo "<td>" . $apt['id'] . "</td>";
    echo "<td>" . $apt['specialist_id'] . "</td>";
    echo "<td>" . $apt['appointment_date'] . "</td>";
    echo "<td>" . $apt['appointment_time'] . "</td>";
    echo "<td>" . $apt['status'] . "</td>";
    echo "</tr>";
  }
  echo "</table>";
  
  echo "<p><strong>Raw JSON:</strong></p>";
  echo "<pre>" . json_encode($appointments, JSON_PRETTY_PRINT) . "</pre>";
}
echo "</div>";

// TEST 3: Test foreign key relationship
echo "<div class='section'>";
echo "<h2>🔗 Test 3: Foreign Key Relationship (Appointments with Specialist Info)</h2>";

if (!empty($appointments)) {
  echo "<p>Testing different foreign key syntaxes...</p>";
  
  // Method 1: Using constraint name
  echo "<h4>Method 1: Using constraint name</h4>";
  $fkTest1 = supabaseSelect(
    'appointments',
    ['user_id' => $user_id],
    'id,specialist_id,appointment_date,appointment_time,status,users!appointments_specialist_id_fkey(fullname,email)',
    'appointment_date.desc',
    1,
    true
  );
  
  if (!empty($fkTest1) && isset($fkTest1[0]['users'])) {
    echo "<p class='success'>✅ Method 1 WORKS!</p>";
    echo "<pre>" . json_encode($fkTest1, JSON_PRETTY_PRINT) . "</pre>";
  } else {
    echo "<p class='error'>❌ Method 1 failed</p>";
    
    // Method 2: Short syntax
    echo "<h4>Method 2: Short syntax</h4>";
    $fkTest2 = supabaseSelect(
      'appointments',
      ['user_id' => $user_id],
      'id,specialist_id,users:specialist_id(fullname,email)',
      'appointment_date.desc',
      1,
      true
    );
    
    if (!empty($fkTest2) && isset($fkTest2[0]['users'])) {
      echo "<p class='success'>✅ Method 2 WORKS!</p>";
      echo "<pre>" . json_encode($fkTest2, JSON_PRETTY_PRINT) . "</pre>";
    } else {
      echo "<p class='error'>❌ Method 2 also failed</p>";
      echo "<p class='warning'>⚠️ Foreign key relationships are not working. Will use fallback method.</p>";
    }
  }
  
  // Fallback method
  echo "<h4>Fallback Method: Separate Queries</h4>";
  $specialistIds = array_unique(array_column($appointments, 'specialist_id'));
  
  if (!empty($specialistIds)) {
    $specialists = supabaseSelect(
      'users',
      ['id' => ['operator' => 'in', 'value' => '(' . implode(',', $specialistIds) . ')']],
      'id,fullname,email,role',
      null,
      null,
      true
    );
    
    if (!empty($specialists)) {
      echo "<p class='success'>✅ Fallback method WORKS!</p>";
      echo "<p>Found " . count($specialists) . " specialist(s):</p>";
      echo "<table>";
      echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
      foreach ($specialists as $spec) {
        echo "<tr>";
        echo "<td>" . $spec['id'] . "</td>";
        echo "<td>" . htmlspecialchars($spec['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($spec['email']) . "</td>";
        echo "<td>" . ($spec['role'] ?? 'N/A') . "</td>";
        echo "</tr>";
      }
      echo "</table>";
    } else {
      echo "<p class='error'>❌ Could not fetch specialists</p>";
    }
  }
} else {
  echo "<p class='warning'>⚠️ No appointments to test foreign key relationships</p>";
}
echo "</div>";

// TEST 4: Test profile update
echo "<div class='section'>";
echo "<h2>✏️ Test 4: Profile Update Capability</h2>";

echo "<p>Testing if profile fields can be updated...</p>";

// Try to update with minimal data
$testUpdateData = [
  'fullname' => $user_name // Just update the same name
];

// Check which fields exist before attempting update
if (isset($user['phone'])) {
  echo "<p class='success'>✅ 'phone' column exists - can be updated</p>";
} else {
  echo "<p class='error'>❌ 'phone' column is MISSING from users table</p>";
}

if (isset($user['address'])) {
  echo "<p class='success'>✅ 'address' column exists - can be updated</p>";
} else {
  echo "<p class='error'>❌ 'address' column is MISSING from users table</p>";
}

if (isset($user['height'])) {
  echo "<p class='success'>✅ 'height' column exists - can be updated</p>";
} else {
  echo "<p class='error'>❌ 'height' column is MISSING from users table</p>";
}

if (isset($user['weight'])) {
  echo "<p class='success'>✅ 'weight' column exists - can be updated</p>";
} else {
  echo "<p class='error'>❌ 'weight' column is MISSING from users table</p>";
}

if (isset($user['blood_group'])) {
  echo "<p class='success'>✅ 'blood_group' column exists - can be updated</p>";
} else {
  echo "<p class='error'>❌ 'blood_group' column is MISSING from users table</p>";
}

if (isset($user['emergency_contact_name'])) {
  echo "<p class='success'>✅ 'emergency_contact_name' column exists - can be updated</p>";
} else {
  echo "<p class='error'>❌ 'emergency_contact_name' column is MISSING from users table</p>";
}

echo "</div>";

// RECOMMENDATIONS
echo "<div class='section'>";
echo "<h2>💡 Diagnosis & Recommendations</h2>";

echo "<h3>Issues Found:</h3>";
echo "<ol>";

$missingColumns = [];
$requiredForProfile = ['phone', 'address', 'height', 'weight', 'blood_group', 'bmi', 
                       'emergency_contact_name', 'emergency_contact_relationship', 'emergency_contact_phone'];

foreach ($requiredForProfile as $col) {
  if (!isset($user[$col])) {
    $missingColumns[] = $col;
  }
}

if (!empty($missingColumns)) {
  echo "<li class='error'><strong>CRITICAL:</strong> Missing columns in users table: " . implode(', ', $missingColumns) . "</li>";
  echo "<ul><li>This is why 'Failed to update profile' error occurs</li>";
  echo "<li>These columns need to be added to your Supabase users table</li></ul>";
}

if (empty($appointments)) {
  echo "<li class='warning'>No appointments found for current user</li>";
  echo "<ul><li>This is why 'No visits yet' and 'No upcoming appointments' appears</li></ul>";
}

if (!empty($appointments) && (empty($fkTest1) || !isset($fkTest1[0]['users'])) && (empty($fkTest2) || !isset($fkTest2[0]['users']))) {
  echo "<li class='warning'>Foreign key relationships not working properly</li>";
  echo "<ul><li>Need to use fallback method to fetch specialist information</li></ul>";
}

echo "</ol>";

echo "<h3>Solutions:</h3>";
echo "<ol>";

if (!empty($missingColumns)) {
  echo "<li><strong>Add Missing Columns to Supabase:</strong>";
  echo "<p>Run these SQL commands in your Supabase SQL Editor:</p>";
  echo "<pre>ALTER TABLE public.users 
ADD COLUMN IF NOT EXISTS phone VARCHAR,
ADD COLUMN IF NOT EXISTS address TEXT,
ADD COLUMN IF NOT EXISTS height INTEGER,
ADD COLUMN IF NOT EXISTS weight NUMERIC,
ADD COLUMN IF NOT EXISTS blood_group VARCHAR,
ADD COLUMN IF NOT EXISTS bmi NUMERIC,
ADD COLUMN IF NOT EXISTS emergency_contact_name VARCHAR,
ADD COLUMN IF NOT EXISTS emergency_contact_relationship VARCHAR,
ADD COLUMN IF NOT EXISTS emergency_contact_phone VARCHAR;</pre>";
  echo "</li>";
}

echo "<li><strong>Use Fixed Files:</strong>";
echo "<ul>";
echo "<li><code>profile_FIXED.php</code> - Fixed profile page with proper error handling</li>";
echo "<li><code>edit-profile_FIXED.php</code> - Fixed edit profile with column checking</li>";
echo "</ul>";
echo "</li>";

echo "</ol>";

echo "</div>";

echo "<div class='section'>";
echo "<h2>🔗 Quick Links</h2>";
echo "<p>";
echo "<a href='profile.php' style='margin-right: 15px;'>View Profile</a>";
echo "<a href='edit-profile.php' style='margin-right: 15px;'>Edit Profile</a>";
echo "<a href='appointments.php' style='margin-right: 15px;'>View Appointments</a>";
echo "<a href='book_appointment.php'>Book Appointment</a>";
echo "</p>";
echo "</div>";
?>