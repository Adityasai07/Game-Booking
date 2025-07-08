<?php
require_once('db.php');

$game_id = $_GET['game_id'] ?? null;
$game_date = $_GET['date'] ?? null;

if (!$game_id || !$game_date) {
    echo "Missing game_id or date.";
    exit;
}

$stmt = $conn->prepare("SELECT id FROM game_dates WHERE game_id = '$game_id' AND game_date = '$game_date'");
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<p>No slots found for this date.</p>";
    exit;
}
$row = $res->fetch_assoc();
$game_date_id = $row['id'];
$stmt->close();

$stmt = $conn->prepare("SELECT id, slot_time, booking_id FROM game_slots WHERE game_date_id = '$game_date_id'");
$stmt->execute();
$result = $stmt->get_result();

$slots = [];
while ($slot = $result->fetch_assoc()) {
    $slots[] = $slot;
}
echo json_encode($slots);


$stmt->close();
?>
