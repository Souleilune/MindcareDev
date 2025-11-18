<?php
/**
 * Supabase Connection Diagnostic Tool
 * Run this file to test your Supabase connection
 * Access: http://localhost/Trial_1/test-connection.php
 */

include 'supabase.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Supabase Connection Test</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0">🔍 Supabase Connection Diagnostic</h3>
      </div>
      <div class="card-body">
        
        <!-- Test 1: Environment Variables -->
        <div class="mb-4">
          <h5>✅ Test 1: Environment Configuration</h5>
          <?php
          $envCheck = [
            'SUPABASE_URL' => !empty(SUPABASE_URL),
            'SUPABASE_KEY' => !empty(SUPABASE_KEY),
            'SUPABASE_SERVICE_KEY' => !empty(SUPABASE_SERVICE_KEY)
          ];
          
          foreach ($envCheck as $key => $isSet) {
            $badgeClass = $isSet ? 'bg-success' : 'bg-danger';
            $status = $isSet ? 'SET' : 'MISSING';
            echo "<div class='mb-2'>";
            echo "<span class='badge $badgeClass'>$status</span> ";
            echo "<strong>$key:</strong> ";
            
            if ($isSet) {
              $value = constant($key);
              // Mask the value for security
              echo substr($value, 0, 30) . '...';
            } else {
              echo "<span class='text-danger'>Not configured in .env file</span>";
            }
            echo "</div>";
          }
          ?>
        </div>

        <!-- Test 2: API Connection -->
        <div class="mb-4">
          <h5>✅ Test 2: API Connectivity</h5>
          <?php
          $testUrl = SUPABASE_URL . '/rest/v1/';
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $testUrl);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . SUPABASE_KEY,
            'Authorization: Bearer ' . SUPABASE_KEY
          ]);
          curl_setopt($ch, CURLOPT_TIMEOUT, 10);
          
          $response = curl_exec($ch);
          $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $curlError = curl_error($ch);
          curl_close($ch);
          
          if ($curlError) {
            echo "<div class='alert alert-danger'>";
            echo "<strong>❌ Connection Failed</strong><br>";
            echo "cURL Error: " . htmlspecialchars($curlError);
            echo "</div>";
          } elseif ($httpCode === 200 || $httpCode === 404) {
            echo "<div class='alert alert-success'>";
            echo "<strong>✅ Connection Successful</strong><br>";
            echo "HTTP Status: $httpCode<br>";
            echo "API is reachable";
            echo "</div>";
          } else {
            echo "<div class='alert alert-warning'>";
            echo "<strong>⚠️ Unexpected Response</strong><br>";
            echo "HTTP Status: $httpCode<br>";
            echo "Response: " . htmlspecialchars(substr($response, 0, 200));
            echo "</div>";
          }
          ?>
        </div>

        <!-- Test 3: Users Table Query -->
        <div class="mb-4">
          <h5>✅ Test 3: Users Table Query</h5>
          <?php
          $users = supabaseSelect('users', [], 'id,email,fullname,role', 'created_at.desc', 5);
          
          if (empty($users)) {
            echo "<div class='alert alert-warning'>";
            echo "<strong>⚠️ No users found or query failed</strong><br>";
            echo "This could mean:<br>";
            echo "• The users table is empty<br>";
            echo "• Row Level Security (RLS) is blocking access<br>";
            echo "• The API key doesn't have proper permissions<br>";
            echo "<br><strong>Debug Info:</strong> Check your PHP error logs for detailed messages.";
            echo "</div>";
          } else {
            echo "<div class='alert alert-success'>";
            echo "<strong>✅ Successfully retrieved " . count($users) . " users</strong>";
            echo "</div>";
            
            echo "<table class='table table-bordered'>";
            echo "<thead><tr><th>ID</th><th>Email</th><th>Full Name</th><th>Role</th></tr></thead>";
            echo "<tbody>";
            foreach ($users as $user) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($user['id']) . "</td>";
              echo "<td>" . htmlspecialchars($user['email']) . "</td>";
              echo "<td>" . htmlspecialchars($user['fullname']) . "</td>";
              echo "<td>" . htmlspecialchars($user['role']) . "</td>";
              echo "</tr>";
            }
            echo "</tbody></table>";
          }
          ?>
        </div>

        <!-- Test 4: Specific Email Lookup -->
        <div class="mb-4">
          <h5>✅ Test 4: Email Lookup Test</h5>
          <form method="GET" class="mb-3">
            <div class="input-group">
              <input type="email" name="test_email" class="form-control" 
                     placeholder="Enter an email to test" 
                     value="<?= htmlspecialchars($_GET['test_email'] ?? '') ?>" required>
              <button type="submit" class="btn btn-primary">Test Lookup</button>
            </div>
          </form>
          
          <?php
          if (isset($_GET['test_email'])) {
            $testEmail = trim($_GET['test_email']);
            echo "<div class='card bg-light'>";
            echo "<div class='card-body'>";
            echo "<strong>Testing email:</strong> " . htmlspecialchars($testEmail) . "<br><br>";
            
            $testUsers = supabaseSelect('users', ['email' => $testEmail]);
            
            if (empty($testUsers)) {
              echo "<span class='badge bg-danger'>NOT FOUND</span><br>";
              echo "<small class='text-muted'>This email does not exist in the database</small>";
            } else {
              echo "<span class='badge bg-success'>FOUND</span><br>";
              echo "<pre class='mt-2'>" . json_encode($testUsers[0], JSON_PRETTY_PRINT) . "</pre>";
            }
            echo "</div></div>";
          }
          ?>
        </div>

        <!-- Recommendations -->
        <div class="alert alert-info">
          <h5>💡 Troubleshooting Tips</h5>
          <ol class="mb-0">
            <li>Check if your <code>.env</code> file exists and has correct credentials</li>
            <li>Verify Supabase Project Settings → API → URL and Keys</li>
            <li>Disable Row Level Security (RLS) temporarily on the users table to test</li>
            <li>Check PHP error logs: <code>tail -f /var/log/apache2/error.log</code></li>
            <li>Ensure cURL is enabled in PHP: <code>php -m | grep curl</code></li>
          </ol>
        </div>

      </div>
    </div>
  </div>
</body>
</html>