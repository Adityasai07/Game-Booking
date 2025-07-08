<?php
session_start();
require_once 'db.php';

$otp = $_POST['otp'];
$newPassword = $_POST['newPassword'];

if (!isset($_SESSION['otp']) || $_SESSION['otp'] != $otp) {
    echo "Invalid OTP";
    exit;
}

$user_id = $_SESSION['reset_user_id'];
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = '$hashedPassword' WHERE id = '$user_id'");

if ($stmt->execute()) {
    unset($_SESSION['otp']);
    unset($_SESSION['reset_user_id']);
    echo "Password reset successfully.";
} else {
    echo "Failed to reset password.";
}
