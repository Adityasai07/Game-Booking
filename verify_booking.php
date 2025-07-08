<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$enteredOtp = $data['otp'] ?? '';

$response = ['valid' => false];

if (
    isset($_SESSION['otp']) &&
    $_SESSION['otp'] == $enteredOtp
) {
    $response['valid'] = true;
}

echo json_encode($response);
