<?php

require_once('db.php');

$username = trim($_POST['username'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$phone || !$password || !$email) {
    echo "All fields are required.";
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]{3,}$/', $username)) {
    echo "Username must be at least 3 characters long and contain only letters, numbers, or underscores.";
    exit;
}

if (!preg_match('/^\d{10}$/', $phone)) {
    echo "Phone number must be exactly 10 digits.";
    exit;
}

if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    echo "Password must be at least 8 characters and include both letters and numbers.";
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$check = $conn->prepare("SELECT id FROM users WHERE username = '$username'");
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "Username already exists.";
    exit;
}
$check->close();



$check2 = $conn->prepare("SELECT id FROM users WHERE phone_number = '$phone'");
$check2->execute();
$check2->store_result();

if ($check2->num_rows > 0) {
    echo "Phone number already exists.";
    exit;
}
$check2->close();

$stmt = $conn->prepare("INSERT INTO users (username, phone_number, email , password) VALUES ('$username', '$phone','$email' , '$hashed')");

if ($stmt->execute()) {
    echo "Signup Success! Now Redirect to login...";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
?>
