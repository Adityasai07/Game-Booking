<?php

require_once('db.php');

$username = trim($_POST['username'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$phone || !$password) {
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

$hashed = password_hash($password, PASSWORD_BCRYPT);

$check = $conn->prepare("SELECT id FROM admins WHERE username = '$username'");
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "Username already exists.";
    exit;
}
$check->close();

$stmt = $conn->prepare("INSERT INTO admins (username, phone, password) VALUES ('$username', '$phone', '$hashed')");

if ($stmt->execute()) {
    echo "Signup Success! Now Redirect to login...";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
?>
