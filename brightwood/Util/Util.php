<?php

namespace Brightwood\Util;

class Util
{
    /**
     * @param (string|null)[]|null $array
     * @return string[]
     */
    public static function clean(?array $array): array
    {
        return array_values(
            array_filter(
                $array ?? [],
                fn (?string $value) => strlen($value) > 0
            )
        );
    }
}
