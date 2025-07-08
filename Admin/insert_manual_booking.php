<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_SESSION['adminuser_id'])) {
    echo json_encode(["success" => false, "message" => "Admin not logged in"]);
    exit;
}

$user_id = intval($_POST['user_id']);
$slot_id = intval($_POST['game_slot_id']);

$insert = $conn->prepare("INSERT INTO bookings (user_id, game_slot_id) VALUES ('$user_id', '$slot_id')");

if ($insert->execute()) {
    $booking_id = $insert->insert_id;

    $update = $conn->prepare("UPDATE game_slots SET booking_id = '$booking_id' WHERE id = '$slot_id'");

    if ($update->execute()) {
        echo json_encode(["success" => true, "message" => "Booking successful!"]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Booking created, but failed to update game_slots: " . $conn->error
        ]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Booking failed: " . $conn->error]);
}
