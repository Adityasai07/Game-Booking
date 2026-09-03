<?php
// Enable full error reporting to see exact errors on screen
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>1. Checking config.json...</h2>";

$configFile = __DIR__ . '/config.json';

if (!file_exists($configFile)) {
    die("<p style='color:red;'>❌ <b>Error:</b> config.json not found in the same folder as this script!</p>");
}

$configData = file_get_contents($configFile);
$config = json_decode($configData, true);

if (!$config) {
    die("<p style='color:red;'>❌ <b>Error:</b> config.json is corrupted or invalid JSON format.</p>");
}

echo "<p style='color:green;'>✔ config.json loaded successfully.</p>";
echo "<b>Host:</b> " . htmlspecialchars($config['host'] ?? '') . "<br>";
echo "<b>User:</b> " . htmlspecialchars($config['username'] ?? '') . "<br>";
echo "<b>DB Name:</b> " . htmlspecialchars($config['dbname'] ?? '') . "<br>";

echo "<h2>2. Testing MySQL Connection...</h2>";

$host = $config['host'] ?? '';
$user = $config['username'] ?? '';
$pass = $config['password'] ?? '';
$dbname = $config['dbname'] ?? '';

// Try connecting via MySQLi
$conn = @new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("<p style='color:red;'>❌ <b>Connection Failed:</b> " . $conn->connect_error . "</p>");
}

echo "<p style='color:green;'>✔ Database connection successful!</p>";

echo "<h2>3. Checking Database Tables...</h2>";

$result = $conn->query("SHOW TABLES");

if ($result && $result->num_rows > 0) {
    echo "<p style='color:green;'>✔ Tables found in database:</p><ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:orange;'>⚠ Database is connected, but no tables were found. Did the SQL import finish properly?</p>";
}

$conn->close();
?>