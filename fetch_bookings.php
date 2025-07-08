<?php
require_once('db.php');
session_start();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$bookings = [];

$stmt = $conn->prepare("
    SELECT 
        b.id AS booking_id,
        g.gamename,
        gs.slot_time,
        gd.game_date
        FROM bookings b
        JOIN game_slots gs ON b.game_slot_id = gs.id
        JOIN game_dates gd ON gs.game_date_id = gd.id
        JOIN games g ON gd.game_id = g.id
        WHERE b.user_id = '$user_id'
        ORDER BY gd.game_date DESC, gs.slot_time DESC
");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $bookings[] = [
        'booking_id' => $row['booking_id'],
        'gamename' => $row['gamename'],
        'slot_time' => $row['slot_time'],
        'game_date' => $row['game_date'],
        'status' => 'Active'
    ];
}
$stmt->close();

$stmt2 = $conn->prepare("
    SELECT 
        cb.booking_id,
        g.gamename,
        gs.slot_time,
        gd.game_date
        FROM cancelled_bookings cb
        LEFT JOIN game_slots gs ON cb.game_slot_id = gs.id
        LEFT JOIN game_dates gd ON gs.game_date_id = gd.id
        LEFT JOIN games g ON gd.game_id = g.id
        WHERE cb.user_id = '$user_id'
        ORDER BY cb.cancelled_at DESC
");
$stmt2->execute();
$result2 = $stmt2->get_result();

while ($row = $result2->fetch_assoc()) {
    $bookings[] = [
        'booking_id' => $row['booking_id'],
        'gamename' => $row['gamename'] ?? 'Unknown Game',
        'slot_time' => $row['slot_time'] ?? 'None',
        'game_date' => $row['game_date'] ?? 'None',
        'status' => 'Cancelled'
    ];
}
$stmt2->close();

$conn->close();

if (empty($bookings)) {
    echo json_encode(["error" => "No bookings found"]);
} else {
    echo json_encode($bookings);
}
?>
