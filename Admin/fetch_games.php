<?php
include 'db.php';
$result = $conn->query("SELECT id, gamename FROM games");
$games = [];
while ($row = $result->fetch_assoc()) {
    $games[] = $row;
}
echo json_encode($games);
?>
