<?php
require_once('db.php');

header('Content-Type: application/json');

if (!isset($_GET['date'])) {
    echo json_encode(['error' => 'Date is required']);
    exit;
}

$date = $_GET['date'];
$games = [];

$query = "
    SELECT g.id, g.gamename, g.description
    FROM games g
    JOIN game_dates gd ON gd.game_id = g.id
    WHERE gd.game_date = '$date'
";

try {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $game_id = $row['id'];
        $game = [
            'id' => $game_id,
            'gamename' => $row['gamename'],
            'description' => $row['description'],
            'images' => []
        ];

        // Fetch images for the current game
        $image_query = "SELECT image_path FROM game_images WHERE game_id = '$game_id' ";
        $image_stmt = $conn->prepare($image_query);
        if (!$image_stmt) {
            throw new Exception('Failed to prepare image statement: ' . $conn->error);
        }
        $image_stmt->execute();
        $image_result = $image_stmt->get_result();
        while ($img = $image_result->fetch_assoc()) {
            $game['images'][] = $img['image_path'];
        }
        $image_stmt->close();

        $games[] = $game;
    }

    $stmt->close();

    if (empty($games)) {
        echo json_encode(['error' => 'No games available on this date']);
        exit;
    }

    echo json_encode($games);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>