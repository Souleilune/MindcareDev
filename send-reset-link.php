<?php
include 'supabase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: forgot-password.php?error=Email not found");
        exit;
    }

    $user = $result->fetch_assoc();
    $user_id = $user['id'];

    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $insert = $conn->prepare("INSERT INTO password_resets (user_id, email, token, expires_at, created_at) VALUES (?, ?, ?, ?, NOW())");
    $insert->bind_param("isss", $user_id, $email, $token, $expires);
    $insert->execute();

    $reset_link = "http://localhost/Trial_1/reset-password.php?token=$token";

    echo "<div style='padding:2rem;font-family:sans-serif'>
        <h3>Reset Link Generated</h3>
        <p>Use this link to reset your password:</p>
        <a href='$reset_link'>$reset_link</a>
        <p><small>This link expires in 1 hour.</small></p>
    </div>";
}
?>