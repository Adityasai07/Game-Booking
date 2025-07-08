<?php
session_start();
require_once 'db.php';
require_once 'email.php';
require_once 'vendor/autoload.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}
$check = $conn->prepare("SELECT email FROM users WHERE id = '$user_id'");
$check->execute();

$result = $check->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}
$check_result = $result->fetch_assoc();
$email = $check_result['email'];
$check->close();


if (!$email) {
    echo json_encode(["success" => false, "message" => "Email not found"]);
    exit;
}
$otp = rand(1000, 9999);
$_SESSION['otp'] = $otp;
$_SESSION['reset_user_id'] = $user_id;

if(sendEmail($email, "Confirm OTP for Booking the Game slot", "Your OTP is: $otp")){
    echo json_encode(["success" => true, "message" => "OTP Email sent"]);
}else{
    echo json_encode(["success" => false, "message" => "OTP Email not send"]);
}
