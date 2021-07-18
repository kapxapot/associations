<?php

namespace App\Bots;

use App\Semantics\Tokenizer;

abstract class AbstractRequest
{
    public const WILDCARD = '*';

    protected Tokenizer $tokenizer;

    protected ?string $applicationId;
    protected ?string $userId;

    protected function __construct()
    {
        $this->tokenizer = new Tokenizer();
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
