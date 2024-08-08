<?php

namespace Brightwood\Util;

use Ramsey\Uuid\Uuid as RamseyUuid;

class Uuid
{
    public static function new(): string
    {
        $uuid = RamseyUuid::uuid4();
        return $uuid->toString();
    }

    public static function isValid(string $uuid): bool
    {
        return preg_match(
            "#^[a-f0-9]{32}$#i",
            str_replace('-', '', $uuid)
        );
    }
}
