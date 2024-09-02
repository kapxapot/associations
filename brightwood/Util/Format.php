<?php

namespace Brightwood\Util;

use DateTime;
use DateTimeZone;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Util\Arrays;

class Format
{
    /**
     * @param string[]|ArrayableInterface $lines
     * @return string[]
     */
    public static function indexLines($lines, ?int $start = null): array
    {
        $lines = array_values(Arrays::adopt($lines));
        $start = $start ?? 0;
        $result = [];

        foreach ($lines as $index => $line) {
            $num = $start + $index + 1;

            $result[] = Join::space("{$num}.", $line);
        }

        return $result;
    }

    public static function utc(string $date): string
    {
        $local = new DateTime(
            $date,
            new DateTimeZone(date_default_timezone_get())
        );

        $local->setTimezone(new DateTimeZone('UTC'));

        return $local->format('d.m.Y'); 
    }

    public static function bold(string $text): string
    {
        return "<b>{$text}</b>";
    }
}
