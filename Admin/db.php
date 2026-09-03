<?php
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);

$host = $config['host'];
$user = $config['user'];
$pass = $config['password'];
$db   = $config['database'];

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
