<?php

namespace App\Bots\Alice;

use App\Bots\AbstractRequest;

class AliceRequest extends AbstractRequest
{
    private const TRASH_TOKENS = [
        'говорю', 'алиса', 'блядь', 'алис', 'сама', 'этот', 'это', 'так', 'ты', 'ой', 'да', 'ну', 'я', 'э', 'а', '-', '='
    ];

    private ?string $originalCommand;
    private ?string $originalUtterance;

    /** @var string[] */
    private array $originalTokens;

    public ?string $command;

    /** @var string[] */
    private array $tokens;

    private bool $isNewSession;

    private ?array $userState;
    private ?array $applicationState;

    private ?string $type;
    private ?string $payload;

    public function __construct(array $data)
    {
        parent::__construct();

        $this->originalCommand = $data['request']['command'] ?? null;
        $this->originalUtterance = $data['request']['original_utterance'] ?? null;
        $this->originalTokens = $data['request']['nlu']['tokens'] ?? [];

        $this->tokens = $this->parseTokens($this->originalUtterance);
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
     * Sanitized command.
     */
    public function command(): ?string
    {
        return $this->command;
    }

    /**
     * Sanitized tokens.
     *
     * @return string[]
     */
    public function tokens(): array
    {
        return $this->tokens;
    }

    /**
     * @return string[]
     */
    private function parseTokens(?string $command): array
    {
        $tokens = $this->tokenizer->tokenize($command);

        return $this->filterTokens($tokens);
    }

    /**
     * Filters trash tokens.
     *
     * @param string[] $tokens
     * @return string[]
     */
    private function filterTokens(array $tokens): array
    {
        $filteredTokens = array_filter(
            $tokens,
            fn (string $t) => !in_array($t, self::TRASH_TOKENS)
        );

        return array_values($filteredTokens);
    }

    /**
     * @param string[] $tokens
     */
    private function rebuildFrom(array $tokens): string
    {
        return $this->tokenizer->join($tokens);
    }

    public function isNewSession(): bool
    {
        return $this->isNewSession;
    }

    public function userState(): ?array
    {
        return $this->userState;
    }

    public function applicationState(): ?array
    {
        return $this->applicationState;
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
        return in_array($this->originalCommand, $commands)
            || in_array($this->command, $commands);
    }

    /**
     * Checks if the request has all the tokens.
     */
    public function has(string ...$tokens): bool
    {
        foreach ($tokens as $token) {
            if (!in_array($token, $this->originalTokens)) {
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
            if (in_array($token, $this->originalTokens)) {
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
}
