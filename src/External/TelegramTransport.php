<?php

namespace App\External;

use App\External\Interfaces\TelegramTransportInterface;
use App\Models\DTO\TelegramBotInfo;
use Exception;

class TelegramTransport implements TelegramTransportInterface
{
    const API_BASE_URL = 'https://api.telegram.org';

    const COMMAND_SEND_MESSAGE = 'sendMessage';
    const COMMAND_GET_CHAT_MEMBER = 'getChatMember';
    const COMMAND_GET_FILE = 'getFile';

    private TelegramBotInfo $botInfo;

    public function __construct(TelegramBotInfo $botInfo)
    {
        $this->botInfo = $botInfo;
    }

    public function botInfo(): TelegramBotInfo
    {
        return $this->botInfo;
    }

    /**
     * @throws Exception
     */
    public function sendMessage(array $message): string
    {
        return $this->executeCommand(self::COMMAND_SEND_MESSAGE, $message);
    }

    /**
     * @throws Exception
     */
    public function executeCommand(string $command, array $payload): string
    {
        $url = $this->getCommandUrl($command);
        $params = $this->serialize($payload);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            throw new Exception('Failed to execute Telegram command: ' . $command);
        }

        return $result;
    }

    public function getFileUrl(string $filePath): string
    {
        return sprintf(
            '%s/file/bot%s/%s',
            self::API_BASE_URL,
            $this->botInfo->token(),
            $filePath
        );
    }

    private function serialize(array $message): array
    {
        return array_map(
            fn ($item) => is_array($item) ? json_encode($item) : $item,
            $message
        );
    }

    private function getCommandUrl(string $command): string
    {
        return sprintf(
            '%s/bot%s/%s',
            self::API_BASE_URL,
            $this->botInfo->token(),
            $command
        );
    }
}
