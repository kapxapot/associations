<?php

namespace App\Bots;

use App\Semantics\Word\Tokenizer;
use Plasticode\Semantics\Attitude;
use Plasticode\Semantics\Gender;

abstract class AbstractBotRequest
{
    public const WILDCARD = '*';

    protected Tokenizer $tokenizer;

    protected ?string $applicationId;
    protected ?string $userId;

    protected bool $isNewSession;

    protected ?string $originalUtterance;

    protected ?string $originalCommand;

    /** @var string[] */
    protected array $originalTokens;

    protected ?string $command;

    /** @var string[] */
    protected array $tokens;

    protected ?array $userState;
    protected ?array $applicationState;

    /** Masculine by default. */
    protected int $gender;

    /** Official by default. */
    protected int $attitude;

    protected bool $isButtonPressed;

    protected function __construct()
    {
        $this->tokenizer = new Tokenizer();

        $this->gender = Gender::MAS;
        $this->attitude = Attitude::OFFICIAL;

        $this->isButtonPressed = false;
    }

    public function hasUser(): bool
    {
        return $this->userId !== null;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function applicationId(): string
    {
        return $this->applicationId;
    }

    public function isNewSession(): bool
    {
        return $this->isNewSession;
    }

    public function originalUtterance(): ?string
    {
        return $this->originalUtterance;
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
    protected function parseTokens(?string $command): array
    {
        $tokens = $this->tokenizer->tokenize($command);

        $trashTokens = $this->getTrashTokens();
        $semiTrashTokens = $this->getSemiTrashTokens();

        // filter trash tokens
        $tokens = $this->filterTokens($tokens, $trashTokens);

        // filter semi-trash tokens (but only if there isn't just one semi-trash token)
        if (count($tokens) === 1 && in_array($tokens[0], $semiTrashTokens)) {
            return $tokens;
        }

        return $this->filterTokens($tokens, $semiTrashTokens);
    }

    /**
     * Removes bad tokens from tokens.
     *
     * @param string[] $tokens
     * @param string[] $badTokens
     * @return string[]
     */
    private function filterTokens(array $tokens, array $badTokens): array
    {
        $filteredTokens = array_filter(
            $tokens,
            fn (string $t) => !in_array($t, $badTokens)
        );

        return array_values($filteredTokens);
    }

    /**
     * Returns semi-trash tokens.
     *
     * These tokens are allowed to be used in case of just one token,
     * but are filtered if there are more than one token.
     *
     * @return string[]
     */
    protected function getSemiTrashTokens(): array
    {
        return [
            'сейчас', 'хорошо', 'блядь', 'блин'
        ];
    }

    /**
     * Returns trash tokens.
     *
     * These tokens are always filtered.
     *
     * @return string[]
     */
    protected function getTrashTokens(): array
    {
        return [
            'давайте', 'сказала', 'сказал', 'говорю', 'ладно', 'давай', 'снова', 'тоже', 'сама', 'этот', 'было', 'была', 'был', 'это', 'эта', 'так', 'сам', 'вот', 'уже', 'дай', 'мне', 'ты', 'ой', 'да', 'ну', 'же', 'я', 'э', 'а', '-', '=', '?'
        ];
    }

    /**
     * @param string[] $tokens
     */
    protected function rebuildFrom(array $tokens): string
    {
        return $this->tokenizer->join($tokens);
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

    public function gender(): int
    {
        return $this->gender;
    }

    public function attitude(): int
    {
        return $this->attitude;
    }

    public function isButtonPressed(): bool
    {
        return $this->isButtonPressed;
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

    /**
     * Checks if the request matches any of the commands.
     */
    public function isAny(string ...$commands): bool
    {
        return in_array($this->originalCommand, $commands)
            || in_array($this->command, $commands);
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
     * Checks if the request matches patterns such as "что такое *" and returns words
     * matched by asterisks.
     *
     * @return string[]|null
     */
    public function matches(string $pattern): ?array
    {
        return $this->matchesTokens($pattern, $this->tokens)
            ?? $this->matchesTokens($pattern, $this->originalTokens);
    }

    /**
     * @param string[] $tokens
     * @return string[]|null
     */
    private function matchesTokens(string $pattern, array $tokens): ?array
    {
        $patternTokens = $this->tokenizer->tokenize($pattern);

        if (count($tokens) !== count($patternTokens)) {
            return null;
        }

        $matches = [];

        for ($i = 0; $i < count($patternTokens); $i++) {
            $token = $patternTokens[$i];

            if ($token === self::WILDCARD) {
                $matches[] = $tokens[$i];
                continue;
            }

            if ($token !== $tokens[$i]) {
                return null;
            }
        }

        return $matches;
    }
}
