<?php

namespace App\Semantics;

use Webmozart\Assert\Assert;

class Severity
{
    const NEUTRAL = 1;
    const OFFENDING = 2;
    const MATURE = 3;

    /**
     * @return array<integer, string>
     */
    public static function allNames(): array
    {
        return [
            self::NEUTRAL => 'neutral',
            self::OFFENDING => 'offending',
            self::MATURE => 'mature',
        ];
    }

    public static function getName(int $severity): string
    {
        $names = self::allNames();
        $name = $names[$severity] ?? null;

        Assert::notNull($name);

        return $name;
    }
}
