<?php

namespace App\Models\DTO;

class AliceRequest
{
    public ?string $command;
    public array $tokens;

    public bool $isNewSession;
    public ?string $userId;
    public string $applicationId;
    public ?array $userState;
    public ?array $applicationState;

    public ?string $type;
    public ?string $payload;

    public function __construct(array $data)
    {
        $this->command = $data['request']['command'] ?? null;
        $this->tokens = $data['request']['nlu']['tokens'] ?? [];
        $this->isNewSession = $data['session']['new'] ?? true;
        $this->userId = $data['session']['user']['user_id'] ?? null;
        $this->applicationId = $data['session']['application']['application_id'] ?? null;
        $this->userState = $data['state']['user'] ?? null;
        $this->applicationState = $data['state']['application'] ?? null;

        $this->type = $data['request']['type'] ?? null;
        $this->payload = $data['request']['payload'] ?? null;
    }

    public function hasUser(): bool
    {
        return $this->userId !== null;
    }

    public function state(): ?array
    {
        return $this->hasUser()
            ? $this->userState
            : $this->applicationState;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function var(string $name, $default = null)
    {
        $state = $this->state();

        if ($state === null) {
            return $default;
        }

        return $state[$name] ?? $default;
    }

    public function buttonPayload(): ?string
    {
        if ($this->type === 'ButtonPressed') {
            return $this->payload;
        }

        return null;
    }
}
