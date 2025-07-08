<?php
require 'db.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');

$now = date("Y-m-d H:i:s");

$sql = "
    SELECT 
    u.username,
    u.phone_number,
    g.gamename, 
    gd.game_date, 
    gs.slot_time,
    CONCAT(gd.game_date, ' ', gs.slot_time) AS full_datetime
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN game_slots gs ON b.game_slot_id = gs.id
    JOIN game_dates gd ON gs.game_date_id = gd.id
    JOIN games g ON gd.game_id = g.id
    ORDER BY gd.game_date DESC, gs.slot_time DESC;
";

$result = $conn->query($sql);

$completed = [];
$upcoming = [];

while ($row = $result->fetch_assoc()) {
    $bookingTime = $row['full_datetime'];
    unset($row['full_datetime']);

    if ($bookingTime > $now) {
        $upcoming[] = $row;
    } else {
        $completed[] = $row;
    }
}

$cancelled = [];
$cancelSql = "
    SELECT 
    u.username,
    u.phone_number,
    g.gamename,
    gd.game_date,
    gs.slot_time,
    cb.reason
    FROM cancelled_bookings cb
    JOIN users u ON cb.user_id = u.id
    JOIN game_slots gs ON cb.game_slot_id = gs.id
    JOIN game_dates gd ON gs.game_date_id = gd.id
    JOIN games g ON gd.game_id = g.id
    ORDER BY gd.game_date DESC, gs.slot_time DESC;
";

$cancelResult = $conn->query($cancelSql);

while ($row = $cancelResult->fetch_assoc()) {
    $cancelled[] = $row;
}

echo json_encode([
    "success" => true,
    "upcoming" => $upcoming,
    "completed" => $completed,
    "cancelled" => $cancelled
]);
?>
