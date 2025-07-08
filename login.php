<?php
session_start();
require_once('db.php');

$username = $_POST['username'];
$password = $_POST['password'];


$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = '$username'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Invalid username or password.";
    exit;
}


$user = $result->fetch_assoc();

if (password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $username;
    echo "Login Success!";
} else {
    echo "Invalid username or password.";
}
?>
