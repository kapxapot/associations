<?php

namespace App\Semantics;

class Tokenizer
{
    const DELIMITER = ' ';

    /**
     * @return string[]
     */
    public function tokenize(?string $str, ?string $delimiter = null): array
    {
        $delimiter ??= self::DELIMITER;

        return strlen($str) > 0
            ? explode($delimiter, $str)
            : [];
    }

    /**
     * @param string[] $tokens
     */
    public function join(array $tokens, ?string $delimiter = null): string
    {
        $delimiter ??= self::DELIMITER;

        return implode($delimiter, $tokens);
    }
}
