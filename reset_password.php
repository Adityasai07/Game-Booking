<?php
session_start();
require 'db.php';

$user_id = $_SESSION['reset_user_id'] ?? null;
$new_pass = $_POST['new_pass'];

if (!$user_id) {
    echo "Session expired";
    exit;
}

$hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = '$hashed_pass' WHERE id = '$user_id'");
$stmt->execute();

echo "Password updated!";
