<?php

namespace App\Models\DTO;

class AliceRequest
{
    private const TOKENS_TO_PURGE = [
        'алиса', 'блядь', 'это', 'да', 'ты', 'ой', 'я', 'а', '-', '='
    ];

    public const WILDCARD = '*';

    public ?string $command;

    /** @var string[] */
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
        $originalCommand = $data['request']['command'] ?? null;

        $this->tokens = $this->parseTokens($originalCommand);
        $this->command = $this->rebuildFrom($this->tokens);

        $this->isNewSession = $data['session']['new'] ?? true;
        $this->userId = $data['session']['user']['user_id'] ?? null;
        $this->applicationId = $data['session']['application']['application_id'] ?? null;
        $this->userState = $data['state']['user'] ?? null;
        $this->applicationState = $data['state']['application'] ?? null;

        $this->type = $data['request']['type'] ?? null;
        $this->payload = $data['request']['payload'] ?? null;
    }

    /**
     * @return string[]
     */
    private function parseTokens(?string $originalCommand): array
    {
        $tokens = explode(' ', $originalCommand);

        if (count($tokens) <= 1) {
            return $tokens;
        }

        $filteredTokens = array_filter(
            $tokens,
            fn (string $t) => !in_array($t, self::TOKENS_TO_PURGE)
        );

        return array_values($filteredTokens);
    }

    /**
     * @param string[] $tokens
     */
    private function rebuildFrom(array $tokens): string
    {
        return implode(' ', $tokens);
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

    /**
     * Checks if the request matches any of the commands.
     */
    public function isAny(string ...$commands): bool
    {
        return in_array($this->command, $commands);
    }

    /**
     * Checks if the request has all the tokens.
     */
    public function has(string ...$tokens): bool
    {
        foreach ($tokens as $token) {
            if (!in_array($token, $this->tokens)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the request has any of the tokens.
     */
    public function hasAny(string ...$tokens): bool
    {
        foreach ($tokens as $token) {
            if (in_array($token, $this->tokens)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the request has all the tokens in any of the token sets.
     * 
     * @param string[] $tokenSets
     */
    public function hasAnySet(array ...$tokenSets): bool
    {
        foreach ($tokenSets as $set) {
            if ($this->has(...$set)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the request matches patterns such as "что такое *".
     */
    public function matches(string $pattern): bool
    {
        $patternTokens = explode(' ', $pattern);

        if (count($this->tokens) !== count($patternTokens)) {
            return false;
        }

        for ($i = 0; $i < count($patternTokens); $i++) {
            $token = $patternTokens[$i];

            if ($token === self::WILDCARD) {
                continue;
            }

            if ($token !== $this->tokens[$i]) {
                return false;
            }
        }

        return true;
    }
}
