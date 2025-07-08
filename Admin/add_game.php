<?php
require_once('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $gamename = $conn->real_escape_string($_POST['gamename'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');

    if (empty($gamename) || empty($description)) {
        echo "Missing game name or description.";
        exit;
    }


    $stmt = $conn->prepare("INSERT INTO games (gamename, description) VALUES ('$gamename' , '$description')");
    $stmt->execute();
    $game_id = $stmt->insert_id;
    //auto_inc , return the value from the most recent insert operation.
    $stmt->close();
    // echo "<script>console.log(  entering );</script>";
    if (!empty($_FILES['images'])) {
        $uploadDir = 'uploads/';
        foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) { // /tmp/php9f8aBc
            if ($_FILES['images']['error'][$index] !== UPLOAD_ERR_OK) {
                error_log("Upload error for index $index: " . $_FILES['images']['error'][$index]);
                continue;
            }

            $fileName = basename($_FILES['images']['name'][$index]);
            $targetPath = $uploadDir . "img_" . $fileName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $stmt = $conn->prepare("INSERT INTO game_images (game_id, image_path) VALUES ('$game_id', '$targetPath')");
                $stmt->execute();
                $stmt->close();
            } else {
                error_log("Failed to move uploaded file: $tmpName");
            }
        }

    }

    echo $game_id;
}
?>
