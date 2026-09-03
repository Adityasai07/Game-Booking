    <?php
    require_once 'vendor/autoload.php';

    function sendEmail($to, $subject, $body) {
        if(!$to){
            return false;   
        }
        $client = new \Google\Client();
        $client->setApplicationName("Game Booking App");
        $client->setAuthConfig('credentials.json');
        $client->addScope("https://www.googleapis.com/auth/gmail.send");
        $client->setAccessType('offline');

        $token = json_decode(file_get_contents("token.json"), true);
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents("token.json", json_encode($client->getAccessToken()));
        }
        try{
            $service = new \Google\Service\Gmail($client);
            $strRawMessage = "To: $to\nSubject: $subject\nMIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\n\n".$body ;

            $raw = strtr(base64_encode($strRawMessage), ['+' => '-', '/' => '_']);
            $message = new \Google\Service\Gmail\Message();
            $message->setRaw($raw);

            $service->users_messages->send('me', $message);
            return true;
        }catch(Exception $e ){
            return false;
        }

    }


    ?>