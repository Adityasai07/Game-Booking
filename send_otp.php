<?php

session_start();
require_once 'db.php';
require_once 'email.php';
require_once 'vendor/autoload.php';

$username = $_POST['username'];
$email = $_POST['email'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username = '$username' AND email = '$email'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    echo "User ID and email do not match.";
    exit;
}
$row = $result->fetch_assoc();
$user_id = $row['id'];
$otp = rand(1000, 9999);
$_SESSION['otp'] = $otp;
$_SESSION['reset_user_id'] = $user_id;


if(sendEmail($email, "OTP to Change password for Game Booking app", "Hi $username , your otp is: $otp")){
    echo "OTP sent to your email.";
}else{
    echo "Falied to send otp.";
}


