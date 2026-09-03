<?php
require_once __DIR__ . '/vendor/autoload.php';

function sendEmail($to, $subject, $messageText) {
    $client = new \Google\Client();
    $client->setApplicationName("Gmail API - PHP");
    $client->setScopes(Google_Service_Gmail::GMAIL_SEND);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');

    $accessToken = json_decode(file_get_contents('token.json'), true);
    $client->setAccessToken($accessToken);

    if ($client->isAccessTokenExpired()) {
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents('token.json', json_encode($client->getAccessToken()));
        } else {
            exit("No refresh token available. Re-authorize using the Python script.");
        }
    }

    $service = new Google_Service_Gmail($client);

    $strRawMessage = "To: {$to}\r\n";
    $strRawMessage .= "Subject: {$subject}\r\n";
    $strRawMessage .= "MIME-Version: 1.0\r\n";
    $strRawMessage .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
    $strRawMessage .= "{$messageText}";

    $rawMessage = base64_encode($strRawMessage);
    $rawMessage = str_replace(['+', '/', '='], ['-', '_', ''], $rawMessage);

    $email = new Google_Service_Gmail_Message();
    $email->setRaw($rawMessage);

    try {
        $sentMessage = $service->users_messages->send("me", $email);
        echo "Email sent successfully. Message ID: " . $sentMessage->getId();
    } catch (Exception $e) {
        echo "Failed to send email: " . $e->getMessage();
    }
}

$to = "adityasai9944@gmail.com";
$subject = "NIce";
$messageText = "good";
sendEmail($to, $subject, $messageText);
