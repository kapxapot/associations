<?php

namespace Brightwood\Util;

class Image
{
    public static function getImageUrl(?string $text): ?string
    {
        if (preg_match('/^https?:\/\/.*\.(png|jpe?g|gif|webp)$/i', $text)) {
            return $text;
        }

        if (preg_match('/^<image>(https?:\/\/.+)<\/image>$/i', $text, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
