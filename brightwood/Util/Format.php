<?php

namespace Brightwood\Util;

use DateTime;
use DateTimeZone;

class Format
{
    public static function utc(string $date): string
    {
        $local = new DateTime(
            $date,
            new DateTimeZone(date_default_timezone_get())
        );

        $local->setTimezone(new DateTimeZone('UTC'));

        return $local->format('d.m.Y'); 
    }
}
