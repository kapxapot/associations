<?php

namespace Brightwood\Util;

class Util
{
    /**
     * @param (string|string[]|null)[]|null $array
     * @return (string|string[])[]
     */
    public static function clean(?array $array): array
    {
        return array_values(
            array_filter(
                $array ?? [],
                fn ($value) => is_array($value) || strlen($value) > 0
            )
        );
    }
}
