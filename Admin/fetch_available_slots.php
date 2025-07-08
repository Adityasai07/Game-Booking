<?php
include 'db.php';
date_default_timezone_set('Asia/Kolkata');
$game_date_id = intval($_GET['game_date_id']);

$result = $conn->query("SELECT game_date FROM game_dates WHERE id = $game_date_id");

if ($result->num_rows === 0) {
    echo json_encode([]);
    exit;
}

$row = $result->fetch_assoc();
$game_date = $row['game_date']; 

$current_time = date('H:i:s'); 
$today = date('Y-m-d');

if ($game_date === $today) {
    $sql = "SELECT gs.id, gs.slot_time 
            FROM game_slots gs 
            LEFT JOIN bookings b ON gs.id = b.game_slot_id 
            WHERE gs.game_date_id = $game_date_id 
              AND b.id IS NULL  
              AND gs.slot_time > '$current_time'
            ORDER BY gs.slot_time";
} else {
    $sql = "SELECT gs.id, gs.slot_time 
            FROM game_slots gs 
            LEFT JOIN bookings b ON gs.id = b.game_slot_id 
            WHERE gs.game_date_id = $game_date_id 
              AND b.id IS NULL 
            ORDER BY gs.slot_time";
}

$res = $conn->query($sql);

$slots = [];
while ($row = $res->fetch_assoc()) {
    $slots[] = $row; 
}

echo json_encode($slots);
?>


