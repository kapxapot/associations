<?php

namespace Brightwood\External;

class TelegramTransport
{
    private string $token;

    public function __construct(
        string $token
    )
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function sendMessage(array $message)
    {
        $url = 'https://api.telegram.org/bot' . $this->token . '/sendMessage';

        $ch = curl_init();

        $params = $this->serialize($message);

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    private function serialize(array $message) : array
    {
        return array_map(
            fn ($item) => is_array($item) ? json_encode($item) : $item,
            $message
        );
    }
}
