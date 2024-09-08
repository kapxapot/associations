<?php

namespace Brightwood\Util;

class Regex
{
    public static function isImageUrl(?string $text): bool
    {
        if (!$text) {
            return false;
        }

        return preg_match('/^https?:\/\/.*\.(png|jpe?g|gif|webp)$/i', $text);
    }
}
