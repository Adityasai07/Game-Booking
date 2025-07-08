<?php
require 'db.php';
header('Content-Type: application/json');

$booking_id = intval($_GET['booking_id'] ?? 0);
if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'Booking ID missing']);
    exit;
}

$sql = "
    SELECT 
        b.id AS booking_id,
        u.username,
        u.phone_number,
        g.gamename,
        g.description,
        gd.game_date,
        gs.slot_time,
        b.booked_at
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN game_slots gs ON b.game_slot_id = gs.id
    JOIN game_dates gd ON gs.game_date_id = gd.id
    JOIN games g ON gd.game_id = g.id
    WHERE b.id = $booking_id
    LIMIT 1
";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    echo json_encode(['success' => true, 'ticket' => $result->fetch_assoc()]);
} else {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
}
?>
