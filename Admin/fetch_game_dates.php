<?php
include 'db.php';
$game_id = intval($_GET['game_id']);
$today = date('Y-m-d');

$stmt = $conn->prepare("SELECT id, game_date FROM game_dates WHERE game_id = '$game_id' AND game_date >= '$today' ORDER BY game_date");
$stmt->execute();
$res = $stmt->get_result();

$dates = [];
while ($row = $res->fetch_assoc()) {
    $dates[] = $row;
}
echo json_encode($dates);
?>
