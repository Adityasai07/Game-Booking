<?php
require_once('db.php');
require_once('email.php');
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$slot_id = $data['slot_id'] ?? null;

if (!$slot_id) {
    echo json_encode(["success" => false, "message" => "Missing slot ID"]);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

// Check if slot is already booked
$check = $conn->query("SELECT booking_id FROM game_slots WHERE id = '$slot_id'");
$check_result = $check->fetch_assoc();

if ($check_result && $check_result['booking_id']) {
    echo json_encode(["success" => false, "message" => "Slot already booked"]);
    exit;
}

// Insert booking
$conn->query("INSERT INTO bookings (user_id, game_slot_id) VALUES ('$user_id', '$slot_id')");
$booking_id = $conn->insert_id;

// Update slot with booking ID
$conn->query("UPDATE game_slots SET booking_id = '$booking_id' WHERE id = '$slot_id'");

// Fetch user info
$user_result = $conn->query("SELECT username, email FROM users WHERE id = '$user_id'");
$user_row = $user_result->fetch_assoc();
$username = $user_row['username'];
$email = $user_row['email'];

// Fetch slot and game details
$details_result = $conn->query("
    SELECT gs.slot_time, gd.game_date, g.gamename 
    FROM game_slots gs
    JOIN game_dates gd ON gs.game_date_id = gd.id
    JOIN games g ON gd.game_id = g.id
    WHERE gs.id = '$slot_id'
");
$details_row = $details_result->fetch_assoc();
$game_slot_time = $details_row['slot_time'];
$game_slot_date = $details_row['game_date'];
$game = $details_row['gamename'];

// Compose and send email
$subject = "Confirm Booking";
$body = "Hi $username,\n\nYour booking for the game: $game is successful.\nSlot Date: $game_slot_date\nSlot Time: $game_slot_time\n\nThank you!";

if (sendEmail($email, $subject, $body)) {
    echo json_encode(["success" => true, "booking_id" => $booking_id, "message" => "Ticket Details sent to Email"]);
} else {
    echo json_encode(["success" => true, "booking_id" => $booking_id, "message" => "Email not sent"]);
}
?>
