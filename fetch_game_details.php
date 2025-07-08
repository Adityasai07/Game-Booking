<?php
require_once('db.php');

$game_id = $_GET['game_id'] ?? null;
if (!$game_id) {
    echo json_encode(["error" => "Missing game ID"]);
    exit;
}

$response = [];

$stmt = $conn->prepare("SELECT gamename, description FROM games WHERE id = '$game_id' ");
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $response['gamename'] = $row['gamename'];
    $response['description'] = $row['description'];
} else {
    echo json_encode(["error" => "Game not found"]);
    exit;
}
$stmt->close();

$response['images'] = [];
$res = $conn->query("SELECT image_path FROM game_images WHERE game_id = $game_id");
while ($img = $res->fetch_assoc()) {
    $response['images'][] = $img['image_path'];
}

echo json_encode($response);
