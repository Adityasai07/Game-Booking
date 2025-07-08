<?php
require_once('db.php');
require_once('email.php');
session_start();
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);
$booking_id = $data['booking_id'] ?? null;
$new_slot_id = $data['new_slot_id'] ?? null;

if (!$new_slot_id) {
    echo json_encode(['success' => false, 'message' => 'Missing new_slot_id']);
    exit;
}


if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'Missing booking_id']);
    exit;
}

if ($booking_id === $new_slot_id) {
    echo json_encode(['success' => false, 'message' => 'Old and new slots cannot be the same']);
    exit;
}

$conn->begin_transaction();

try {
    // Step 1: Fetch current booking
    $booking_res = $conn->query("SELECT user_id, game_slot_id FROM bookings WHERE id = '$booking_id'");
    if (!$booking_res || $booking_res->num_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }

    $booking = $booking_res->fetch_assoc();
    $user_id = $booking['user_id'];
    $old_slot_id = $booking['game_slot_id'];

    if ($old_slot_id == $new_slot_id) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'New slot is same as current slot']);
        exit;
    }

    // Step 2: Ensure new slot exists and is unbooked
    $slot_check = $conn->query("SELECT booking_id FROM game_slots WHERE id = '$new_slot_id'");
    if (!$slot_check || $slot_check->num_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'New slot does not exist']);
        exit;
    }

    $slot_data = $slot_check->fetch_assoc();
    if (!is_null($slot_data['booking_id'])) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'New slot is already booked']);
        exit;
    }

    // Step 3: Free old slot
    $conn->query("UPDATE game_slots SET booking_id = NULL WHERE id = '$old_slot_id'");

    // Step 4: Assign booking to new slot
    $conn->query("UPDATE game_slots SET booking_id = '$booking_id' WHERE id = '$new_slot_id'");

    // Step 5: Update booking with new slot_id
    $conn->query("UPDATE bookings SET game_slot_id = '$new_slot_id' WHERE id = '$booking_id'");

    // Step 6: Fetch user info
    $user_res = $conn->query("SELECT username, email FROM users WHERE id = '$user_id'");
    $user = $user_res->fetch_assoc();
    $username = $user['username'];
    $email = $user['email'];

    // Step 7: Fetch new slot info
    $slot_info = $conn->query("
        SELECT gs.slot_time, gd.game_date, g.gamename 
        FROM game_slots gs
        JOIN game_dates gd ON gs.game_date_id = gd.id
        JOIN games g ON gd.game_id = g.id
        WHERE gs.id = '$new_slot_id'
    ");
    $slot_details = $slot_info->fetch_assoc();
    $slot_time = $slot_details['slot_time'] ?? 'Unknown';
    $slot_date = $slot_details['game_date'] ?? 'Unknown';
    $game = $slot_details['gamename'] ?? 'Unknown';

    // Step 8: Commit transaction
    $conn->commit();

    // Step 9: Send email
    $subject = "Booking Rescheduled";
    $body = "Hi $username,\n\nYour booking for the game: $game has been successfully rescheduled.\nNew Slot Date: $slot_date\nNew Slot Time: $slot_time\n\nThank you!";

    if (sendEmail($email, $subject, $body)) {
        echo json_encode(['success' => true, 'message' => 'Reschedule successful. Email sent.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Reschedule successful. Email not sent.']);
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Reschedule failed.']);
}

$conn->close();
?>
