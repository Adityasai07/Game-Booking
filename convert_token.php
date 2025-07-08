<?php
$pythonToken = json_decode(file_get_contents("token_send.json"), true);
if (empty($pythonToken['token']) || empty($pythonToken['expiry'])) {
    die("Missing access_token or expiry in token.json\n");
}

$expiryTimestamp = strtotime($pythonToken['expiry']);
$created = $expiryTimestamp - (60 * 60 * 24 * 7);

$phpToken = [
    "access_token" => $pythonToken['token'],
    "refresh_token" => $pythonToken['refresh_token'] ?? '',
    "scope" => implode(" ", $pythonToken['scopes']),
    "token_type" => "Bearer",
    "created" => $created
];

file_put_contents("token.json", json_encode($phpToken, JSON_PRETTY_PRINT));

echo " token.json converted successfully for PHP Gmail API use.\n";
