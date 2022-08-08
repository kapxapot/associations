<?php

namespace App\Models\DTO;

class TelegramBotInfo
{
    private string $token;
    private ?int $id = null;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function id(): int
    {
        if ($this->id === null) {
            $tokenParts = explode(':', $this->token);
            $this->id = intval($tokenParts[0]);
        }

        return $this->id;
    }
}
