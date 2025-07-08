<?php
session_start();

if (isset($_SESSION['adminuser_id'])) {
    echo json_encode([
        "success" => true,
        "username" => $_SESSION['adminusername']
    ]);
} else {
    echo json_encode(["success" => false]);
}
?>
