<?php

namespace App\Semantics;

use Plasticode\Util\Strings;

class SentenceCleaner
{
    const DOT = '.';

    /**
     * Trims one and only one trailing dot.
     * If there are more than one dots, doesn't trim anything.
     */
    public function trimTrailingDot(?string $sentence): ?string
    {
        if ($sentence === null) {
            return null;
        }

        $trimmed = $sentence;

        while (Strings::last($trimmed) === self::DOT) {
            $trimmed = Strings::trimEnd($trimmed, self::DOT);
        }

        // return trimmed in case of removal of one char,
        // otherwise return the original string
        return mb_strlen($sentence) === mb_strlen($trimmed) + 1
            ? $trimmed
            : $sentence;
    }
}
