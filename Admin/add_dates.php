<?php
require_once('db.php');

$data = json_decode(file_get_contents('php://input'), true);
$game_id = intval($data['game_id']);
$dates = $data['dates'];

if (!$game_id || empty($dates)) {
    echo "Invalid data";
    exit;
}

$slot_times = ["09:00" , "09:30" ,  "10:00","10:30", "11:00", "11:30" , "12:00", "12:30" , "13:00", "13:30", "14:00", "14:30","15:00",  "15:30",   "16:00","16:30"];

foreach ($dates as $date) {
    $stmt = $conn->prepare("INSERT IGNORE INTO game_dates (game_id, game_date) VALUES ('$game_id', '$date')");
    $stmt->execute();
    $stmt->close();

    $res = $conn->query("SELECT id FROM game_dates WHERE game_id = '$game_id' AND game_date = '$date'");
    $row = $res->fetch_assoc();
    $game_date_id = $row['id'];

    foreach ($slot_times as $time) {
        $stmt = $conn->prepare("INSERT INTO game_slots (game_date_id, slot_time) VALUES ('$game_date_id', '$time')");
        $stmt->execute();
        $stmt->close();
    }
}

?>
