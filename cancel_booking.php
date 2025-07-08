<?php
require_once('db.php');
require_once('email.php');
session_start();
header('Content-Type: application/json');

$booking_id = $_GET['booking_id'] ?? null;
$reason = trim($_GET['reason'] ?? ''); // ✅ Get reason from query string

if (!$booking_id) {
    echo json_encode(['error' => 'No booking ID provided']);
    exit;
}

// Step 1: Get user_id and game_slot_id
$result = $conn->query("SELECT user_id, game_slot_id FROM bookings WHERE id = '$booking_id'");
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(['error' => 'Booking not found']);
    exit;
}

$user_id = $row['user_id'];
$game_slot_id = $row['game_slot_id'];

// Start transaction
$conn->begin_transaction();

try {
    // ✅ Archive booking with reason
    $conn->query("
        INSERT INTO cancelled_bookings (booking_id, user_id, game_slot_id, reason)
        VALUES ('$booking_id', '$user_id', '$game_slot_id', '$reason')
    ");

    // Update slot → make available
    $conn->query("UPDATE game_slots SET booking_id = NULL WHERE id = '$game_slot_id'");

    // Clear booking slot reference
    $conn->query("UPDATE bookings SET game_slot_id = NULL WHERE id = '$booking_id'");

    // Get email details AFTER database changes (still inside transaction)
    $user_result = $conn->query("SELECT username, email FROM users WHERE id = '$user_id'");
    $user = $user_result->fetch_assoc();
    $username = $user['username'];
    $email = $user['email'];

    $details_result = $conn->query("
        SELECT gs.slot_time, gd.game_date, g.gamename 
        FROM game_slots gs
        JOIN game_dates gd ON gs.game_date_id = gd.id
        JOIN games g ON gd.game_id = g.id
        WHERE gs.id = '$game_slot_id'
    ");
    $details = $details_result->fetch_assoc();
    $slot_time = $details['slot_time'] ?? 'Unknown';
    $slot_date = $details['game_date'] ?? 'Unknown';
    $gamename = $details['gamename'] ?? 'Unknown';

    // Commit DB changes
    $conn->commit();

    // Send cancellation email
    $subject = "Booking Cancelled";
    $body = "Hi $username,\n\nYour booking for the game: $gamename has been cancelled.\nSlot Date: $slot_date\nSlot Time: $slot_time\n\nReason: $reason\n\nThank you."; // ✅ Include reason in email

    if (sendEmail($email, $subject, $body)) {
        echo json_encode(['success' => true, 'message' => 'Cancellation successful, email sent']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Cancellation successful, but email failed']);
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => 'Cancellation failed']);
}

$conn->close();
?>
