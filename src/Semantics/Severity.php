<?php

namespace App\Semantics;

class Severity
{
    const NEUTRAL = 1;
    const OFFENDING = 2;
    const MATURE = 3;

    public static function isMature(int $severity): bool
    {
        return $severity == self::MATURE;
    }
}
