<?php

namespace Brightwood\Util;

class Join
{
    public static function space(?string ...$words): string
    {
        return self::clean($words);
    }

    public static function underline(?string ...$words): string
    {
        return self::clean($words, '_');
    }

    /**
     * @param (string|null)[] $words
     */
    public static function clean(array $words, ?string $delimiter = null): string
    {
        return implode(
            $delimiter ?? ' ',
            Util::clean($words)
        );
    }
}
