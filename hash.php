<?php
$password = 'admin123'; // You can change this to any password you want
$hashed = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed password: " . $hashed;
?>