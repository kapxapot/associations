<?php

namespace App\External\Interfaces;

use App\Models\DTO\TelegramBotInfo;

interface TelegramTransportInterface
{
    public function botInfo(): TelegramBotInfo;

    public function sendMessage(array $message): string;

    public function sendPhoto(array $message): string;

    public function executeCommand(string $command, array $payload): string;

    public function getFileUrl(string $filePath): string;
}
